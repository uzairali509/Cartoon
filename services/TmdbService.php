<?php
/**
 * TmdbService.php — TMDB API Service Layer
 * 
 * Handles all TMDB API interactions with caching, rate limiting, and error handling.
 * All API keys stay server-side.
 */

require_once __DIR__ . '/../config/api.php';

class TmdbService {
    private string $apiKey;
    private string $baseUrl;
    private string $imageBaseUrl;
    private int $cacheTtl;
    private string $cacheDir;
    private float $lastRequestTime = 0;
    private int $requestCount = 0;
    private const RATE_LIMIT = 40; // requests per 10 seconds
    private const RATE_WINDOW = 10; // seconds
    private bool $apiKeyConfigured = false;

    public function __construct(?string $apiKey = null) {
        $this->apiKey = $apiKey ?? TMDB_API_KEY;
        $this->baseUrl = TMDB_BASE_URL;
        $this->imageBaseUrl = TMDB_IMAGE_BASE_URL;
        $this->cacheTtl = API_CACHE_TTL;
        $this->cacheDir = API_CACHE_DIR;
        $this->apiKeyConfigured = !empty($this->apiKey);
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool {
        return $this->apiKeyConfigured;
    }

    /**
     * Make a GET request to TMDB API with caching and rate limiting
     */
    private function request(string $endpoint, array $params = []): ?array {
        // Check if API key is configured
        if (!$this->apiKeyConfigured) {
            error_log("TMDB API Error: API key not configured. Please set TMDB_API_KEY in your .env file.");
            return ['error' => 'API key not configured', 'results' => []];
        }

        // Rate limiting
        $this->enforceRateLimit();

        $params['api_key'] = $this->apiKey;
        $params['language'] = $params['language'] ?? 'en-US';
        
        $queryString = http_build_query($params);
        $url = $this->baseUrl . $endpoint . '?' . $queryString;
        $cacheKey = md5($url);
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.json';

        // Check cache
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtl) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if ($cached !== false) {
                return $cached;
            }
        }

        // Make request
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ];
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            error_log("TMDB API Error: HTTP $httpCode for $url");
            return ['error' => "HTTP $httpCode", 'results' => []];
        }

        $data = json_decode($response, true);
        if ($data === null) {
            error_log("TMDB API Error: Invalid JSON response for $url");
            return ['error' => 'Invalid JSON', 'results' => []];
        }

        // Cache successful response
        file_put_contents($cacheFile, json_encode($data));

        return $data;
    }

    /**
     * Enforce TMDB rate limiting (40 requests per 10 seconds)
     */
    private function enforceRateLimit(): void {
        $now = microtime(true);
        
        // Reset counter if window has passed
        if ($now - $this->lastRequestTime > self::RATE_WINDOW) {
            $this->requestCount = 0;
            $this->lastRequestTime = $now;
        }

        // If we've hit the limit, wait
        if ($this->requestCount >= self::RATE_LIMIT) {
            $waitTime = self::RATE_WINDOW - ($now - $this->lastRequestTime);
            if ($waitTime > 0) {
                usleep($waitTime * 1000000);
            }
            $this->requestCount = 0;
            $this->lastRequestTime = microtime(true);
        }

        $this->requestCount++;
    }

    /**
     * Get full image URL
     */
    public function getImageUrl(string $path, string $size = TMDB_POSTER_SIZE): string {
        if (empty($path)) {
            return '';
        }
        return $this->imageBaseUrl . '/' . $size . $path;
    }

    /**
     * Get backdrop URL
     */
    public function getBackdropUrl(string $path, string $size = TMDB_BACKDROP_SIZE): string {
        if (empty($path)) {
            return '';
        }
        return $this->imageBaseUrl . '/' . $size . $path;
    }

    /**
     * Get profile URL (for cast/crew)
     */
    public function getProfileUrl(string $path, string $size = TMDB_PROFILE_SIZE): string {
        if (empty($path)) {
            return '';
        }
        return $this->imageBaseUrl . '/' . $size . $path;
    }

    // ==================== DISCOVER / SEARCH ====================

    /**
     * Discover animated TV shows
     */
    public function discoverAnimatedShows(array $options = []): ?array {
        $params = array_merge([
            'with_genres' => TMDB_ANIMATION_GENRE_ID,
            'sort_by' => 'popularity.desc',
            'include_adult' => false,
            'include_null_first_air_dates' => false,
            'with_type' => 'tv',
        ], $options);

        return $this->request('/discover/tv', $params);
    }

    /**
     * Discover animated movies
     */
    public function discoverAnimatedMovies(array $options = []): ?array {
        $params = array_merge([
            'with_genres' => TMDB_ANIMATION_GENRE_ID,
            'sort_by' => 'popularity.desc',
            'include_adult' => false,
            'include_video' => false,
        ], $options);

        return $this->request('/discover/movie', $params);
    }

    /**
     * Search for TV shows and movies
     */
    public function searchMulti(string $query, int $page = 1): ?array {
        return $this->request('/search/multi', [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ]);
    }

    /**
     * Search TV shows
     */
    public function searchTv(string $query, int $page = 1): ?array {
        return $this->request('/search/tv', [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ]);
    }

    /**
     * Search movies
     */
    public function searchMovie(string $query, int $page = 1): ?array {
        return $this->request('/search/movie', [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ]);
    }

    /**
     * Search people (for characters/actors)
     */
    public function searchPerson(string $query, int $page = 1): ?array {
        return $this->request('/search/person', [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ]);
    }

    /**
     * Get popular people (actors, voice actors, etc.)
     */
    public function getPopularPeople(int $page = 1): ?array {
        return $this->request('/person/popular', [
            'page' => $page,
        ]);
    }

    // ==================== TV SHOWS ====================

    /**
     * Get TV show details
     */
    public function getTvDetails(int $tvId): ?array {
        return $this->request("/tv/$tvId", [
            'append_to_response' => 'credits,videos,images,external_ids,content_ratings,recommendations,similar',
        ]);
    }

    /**
     * Get TV show seasons
     */
    public function getTvSeasons(int $tvId): ?array {
        return $this->request("/tv/$tvId", [
            'append_to_response' => 'seasons',
        ]);
    }

    /**
     * Get TV show episodes for a season
     */
    public function getSeasonEpisodes(int $tvId, int $seasonNumber): ?array {
        return $this->request("/tv/$tvId/season/$seasonNumber");
    }

    /**
     * Get TV show episode details
     */
    public function getEpisodeDetails(int $tvId, int $seasonNumber, int $episodeNumber): ?array {
        return $this->request("/tv/$tvId/season/$seasonNumber/episode/$episodeNumber");
    }

    /**
     * Get TV show credits (cast/crew)
     */
    public function getTvCredits(int $tvId): ?array {
        return $this->request("/tv/$tvId/credits");
    }

    /**
     * Get TV show videos (trailers, teasers)
     */
    public function getTvVideos(int $tvId): ?array {
        return $this->request("/tv/$tvId/videos");
    }

    /**
     * Get TV show images (posters, backdrops)
     */
    public function getTvImages(int $tvId): ?array {
        return $this->request("/tv/$tvId/images");
    }

    /**
     * Get TV show external IDs (IMDb, etc.)
     */
    public function getTvExternalIds(int $tvId): ?array {
        return $this->request("/tv/$tvId/external_ids");
    }

    /**
     * Get TV show content ratings
     */
    public function getTvContentRatings(int $tvId): ?array {
        return $this->request("/tv/$tvId/content_ratings");
    }

    /**
     * Get similar TV shows
     */
    public function getSimilarTvShows(int $tvId, int $page = 1): ?array {
        return $this->request("/tv/$tvId/similar", ['page' => $page]);
    }

    /**
     * Get TV show recommendations
     */
    public function getTvRecommendations(int $tvId, int $page = 1): ?array {
        return $this->request("/tv/$tvId/recommendations", ['page' => $page]);
    }

    /**
     * Get TV show keywords
     */
    public function getTvKeywords(int $tvId): ?array {
        return $this->request("/tv/$tvId/keywords");
    }

    /**
     * Get TV show watch providers
     */
    public function getTvWatchProviders(int $tvId): ?array {
        return $this->request("/tv/$tvId/watch/providers");
    }

    // ==================== MOVIES ====================

    /**
     * Get movie details
     */
    public function getMovieDetails(int $movieId): ?array {
        return $this->request("/movie/$movieId", [
            'append_to_response' => 'credits,videos,images,external_ids,release_dates,recommendations,similar,keywords',
        ]);
    }

    /**
     * Get movie credits
     */
    public function getMovieCredits(int $movieId): ?array {
        return $this->request("/movie/$movieId/credits");
    }

    /**
     * Get movie videos
     */
    public function getMovieVideos(int $movieId): ?array {
        return $this->request("/movie/$movieId/videos");
    }

    /**
     * Get movie images
     */
    public function getMovieImages(int $movieId): ?array {
        return $this->request("/movie/$movieId/images");
    }

    /**
     * Get similar movies
     */
    public function getSimilarMovies(int $movieId, int $page = 1): ?array {
        return $this->request("/movie/$movieId/similar", ['page' => $page]);
    }

    /**
     * Get movie recommendations
     */
    public function getMovieRecommendations(int $movieId, int $page = 1): ?array {
        return $this->request("/movie/$movieId/recommendations", ['page' => $page]);
    }

    // ==================== PEOPLE / CHARACTERS ====================

    /**
     * Get person details (actor, voice actor, creator)
     */
    public function getPersonDetails(int $personId): ?array {
        return $this->request("/person/$personId", [
            'append_to_response' => 'combined_credits,images,external_ids',
        ]);
    }

    /**
     * Get person combined credits
     */
    public function getPersonCredits(int $personId): ?array {
        return $this->request("/person/$personId/combined_credits");
    }

    /**
     * Get person images
     */
    public function getPersonImages(int $personId): ?array {
        return $this->request("/person/$personId/images");
    }

    // ==================== GENRES ====================

    /**
     * Get TV genres
     */
    public function getTvGenres(): ?array {
        return $this->request('/genre/tv/list');
    }

    /**
     * Get movie genres
     */
    public function getMovieGenres(): ?array {
        return $this->request('/genre/movie/list');
    }

    // ==================== TRENDING ====================

    /**
     * Get trending TV shows
     */
    public function getTrendingTv(string $timeWindow = 'week', int $page = 1): ?array {
        return $this->request("/trending/tv/$timeWindow", ['page' => $page]);
    }

    /**
     * Get trending movies
     */
    public function getTrendingMovies(string $timeWindow = 'week', int $page = 1): ?array {
        return $this->request("/trending/movie/$timeWindow", ['page' => $page]);
    }

    // ==================== POPULAR / TOP RATED ====================

    /**
     * Get popular TV shows
     */
    public function getPopularTv(int $page = 1): ?array {
        return $this->request('/tv/popular', ['page' => $page]);
    }

    /**
     * Get top rated TV shows
     */
    public function getTopRatedTv(int $page = 1): ?array {
        return $this->request('/tv/top_rated', ['page' => $page]);
    }

    /**
     * Get popular movies
     */
    public function getPopularMovies(int $page = 1): ?array {
        return $this->request('/movie/popular', ['page' => $page]);
    }

    /**
     * Get top rated movies
     */
    public function getTopRatedMovies(int $page = 1): ?array {
        return $this->request('/movie/top_rated', ['page' => $page]);
    }

    // ==================== NOW PLAYING / AIRING TODAY ====================

    /**
     * Get TV shows airing today
     */
    public function getTvAiringToday(int $page = 1): ?array {
        return $this->request('/tv/airing_today', ['page' => $page]);
    }

    /**
     * Get TV shows on the air
     */
    public function getTvOnTheAir(int $page = 1): ?array {
        return $this->request('/tv/on_the_air', ['page' => $page]);
    }

    /**
     * Get now playing movies
     */
    public function getNowPlayingMovies(int $page = 1): ?array {
        return $this->request('/movie/now_playing', ['page' => $page]);
    }

    // ==================== COUNTRIES / REGIONS ====================

    /**
     * Get countries with TV content
     */
    public function getCountries(): ?array {
        return $this->request('/configuration/countries');
    }

    /**
     * Get languages
     */
    public function getLanguages(): ?array {
        return $this->request('/configuration/languages');
    }

    /**
     * Discover TV by country
     */
    public function discoverTvByCountry(string $countryCode, array $options = []): ?array {
        $params = array_merge([
            'with_origin_country' => $countryCode,
            'sort_by' => 'popularity.desc',
        ], $options);

        return $this->request('/discover/tv', $params);
    }

    // ==================== NETWORKS / COMPANIES ====================

    /**
     * Get TV networks
     */
    public function getNetworks(): ?array {
        return $this->request('/network/list');
    }

    /**
     * Get production companies
     */
    public function getCompanies(): ?array {
        return $this->request('/company/list');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Format TV show data for frontend
     */
    public function formatTvShow(array $show): array {
        return [
            'id' => $show['id'],
            'tmdb_id' => $show['id'],
            'title' => $show['name'] ?? $show['title'] ?? '',
            'original_title' => $show['original_name'] ?? $show['original_title'] ?? '',
            'overview' => $show['overview'] ?? '',
            'poster_path' => $show['poster_path'] ?? '',
            'backdrop_path' => $show['backdrop_path'] ?? '',
            'poster_url' => $this->getImageUrl($show['poster_path'] ?? ''),
            'backdrop_url' => $this->getBackdropUrl($show['backdrop_path'] ?? ''),
            'first_air_date' => $show['first_air_date'] ?? $show['release_date'] ?? '',
            'last_air_date' => $show['last_air_date'] ?? '',
            'vote_average' => $show['vote_average'] ?? 0,
            'vote_count' => $show['vote_count'] ?? 0,
            'popularity' => $show['popularity'] ?? 0,
            'genre_ids' => $show['genre_ids'] ?? [],
            'genres' => $show['genres'] ?? [],
            'origin_country' => $show['origin_country'] ?? [],
            'original_language' => $show['original_language'] ?? '',
            'status' => $show['status'] ?? '',
            'type' => $show['type'] ?? '',
            'number_of_seasons' => $show['number_of_seasons'] ?? 0,
            'number_of_episodes' => $show['number_of_episodes'] ?? 0,
            'episode_run_time' => $show['episode_run_time'] ?? [],
            'networks' => $show['networks'] ?? [],
            'production_companies' => $show['production_companies'] ?? [],
            'production_countries' => $show['production_countries'] ?? [],
            'spoken_languages' => $show['spoken_languages'] ?? [],
            'tagline' => $show['tagline'] ?? '',
            'homepage' => $show['homepage'] ?? '',
            'in_production' => $show['in_production'] ?? false,
        ];
    }

    /**
     * Format movie data for frontend
     */
    public function formatMovie(array $movie): array {
        return [
            'id' => $movie['id'],
            'tmdb_id' => $movie['id'],
            'title' => $movie['title'] ?? '',
            'original_title' => $movie['original_title'] ?? '',
            'overview' => $movie['overview'] ?? '',
            'poster_path' => $movie['poster_path'] ?? '',
            'backdrop_path' => $movie['backdrop_path'] ?? '',
            'poster_url' => $this->getImageUrl($movie['poster_path'] ?? ''),
            'backdrop_url' => $this->getBackdropUrl($movie['backdrop_path'] ?? ''),
            'release_date' => $movie['release_date'] ?? '',
            'vote_average' => $movie['vote_average'] ?? 0,
            'vote_count' => $movie['vote_count'] ?? 0,
            'popularity' => $movie['popularity'] ?? 0,
            'genre_ids' => $movie['genre_ids'] ?? [],
            'genres' => $movie['genres'] ?? [],
            'original_language' => $movie['original_language'] ?? '',
            'status' => $movie['status'] ?? '',
            'runtime' => $movie['runtime'] ?? 0,
            'budget' => $movie['budget'] ?? 0,
            'revenue' => $movie['revenue'] ?? 0,
            'production_companies' => $movie['production_companies'] ?? [],
            'production_countries' => $movie['production_countries'] ?? [],
            'spoken_languages' => $movie['spoken_languages'] ?? [],
            'tagline' => $movie['tagline'] ?? '',
            'homepage' => $movie['homepage'] ?? '',
            'video' => $movie['video'] ?? false,
        ];
    }

    /**
     * Format person/character data
     */
    public function formatPerson(array $person): array {
        // Ensure required fields exist with defaults
        $id = $person['id'] ?? 0;
        
        return [
            'id' => $id,
            'tmdb_id' => $id,
            'name' => $person['name'] ?? 'Unknown',
            'profile_path' => $person['profile_path'] ?? '',
            'profile_url' => $this->getProfileUrl($person['profile_path'] ?? ''),
            'known_for_department' => $person['known_for_department'] ?? '',
            'gender' => $person['gender'] ?? 0,
            'popularity' => $person['popularity'] ?? 0,
            'known_for' => $person['known_for'] ?? [],
        ];
    }

    /**
     * Format season data
     */
    public function formatSeason(array $season): array {
        return [
            'id' => $season['id'],
            'season_number' => $season['season_number'] ?? 0,
            'name' => $season['name'] ?? '',
            'overview' => $season['overview'] ?? '',
            'poster_path' => $season['poster_path'] ?? '',
            'poster_url' => $this->getImageUrl($season['poster_path'] ?? ''),
            'air_date' => $season['air_date'] ?? '',
            'episode_count' => $season['episode_count'] ?? 0,
        ];
    }

    /**
     * Format episode data
     */
    public function formatEpisode(array $episode): array {
        return [
            'id' => $episode['id'],
            'episode_number' => $episode['episode_number'] ?? 0,
            'season_number' => $episode['season_number'] ?? 0,
            'name' => $episode['name'] ?? '',
            'overview' => $episode['overview'] ?? '',
            'still_path' => $episode['still_path'] ?? '',
            'still_url' => $this->getImageUrl($episode['still_path'] ?? ''),
            'air_date' => $episode['air_date'] ?? '',
            'runtime' => $episode['runtime'] ?? 0,
            'vote_average' => $episode['vote_average'] ?? 0,
            'vote_count' => $episode['vote_count'] ?? 0,
        ];
    }

    /**
     * Clear all cache
     */
    public function clearCache(): void {
        $files = glob($this->cacheDir . '/*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}