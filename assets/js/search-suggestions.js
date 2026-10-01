// Search Suggestions for Cartoon Universe
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = searchForm?.querySelector('input[name="q"]');
    const suggestionsBox = document.getElementById('searchSuggestions');
    let debounceTimer;
    let currentFocus = -1;
    
    if (!searchInput || !suggestionsBox) return;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideSuggestions();
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 250);
    });
    
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            fetchSuggestions(this.value.trim());
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!searchForm.contains(e.target)) {
            hideSuggestions();
        }
    });
    
    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsBox.querySelectorAll('.search-suggestion-item');
        if (!items.length) return;
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus = Math.min(currentFocus + 1, items.length - 1);
            updateFocus(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus = Math.max(currentFocus - 1, 0);
            updateFocus(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus >= 0 && items[currentFocus]) {
                items[currentFocus].click();
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
            this.blur();
        }
    });
    
    function updateFocus(items) {
        items.forEach((item, i) => {
            item.classList.toggle('focused', i === currentFocus);
        });
        if (currentFocus >= 0) {
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
    }
    
    function hideSuggestions() {
        suggestionsBox.hidden = true;
        suggestionsBox.innerHTML = '';
        currentFocus = -1;
    }
    
    async function fetchSuggestions(query) {
        try {
            const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}&limit=6`);
            const data = await response.json();
            
            if (data.success && data.results.length > 0) {
                renderSuggestions(data.results);
            } else {
                hideSuggestions();
            }
        } catch (e) {
            console.error('Search suggestions error:', e);
            hideSuggestions();
        }
    }
    
    function renderSuggestions(results) {
        suggestionsBox.innerHTML = '';
        currentFocus = -1;
        
        results.forEach((item, index) => {
            const link = document.createElement('a');
            link.href = item.media_type === 'tv' ? `show.php?id=${item.tmdb_id}` : `movie.php?id=${item.tmdb_id}`;
            link.className = 'search-suggestion-item';
            link.dataset.index = index;
            
            link.innerHTML = `
                ${item.poster_url ? `<img src="${item.poster_url}" alt="" loading="lazy">` : '<div class="suggestion-placeholder"></div>'}
                <div class="search-suggestion-info">
                    <h4>${escapeHtml(item.title)}</h4>
                    <span>${item.media_type === 'tv' ? 'TV Show' : 'Movie'}${item.first_air_date ? ' • ' + item.first_air_date.slice(0, 4) : ''}${item.release_date ? ' • ' + item.release_date.slice(0, 4) : ''}</span>
                </div>
            `;
            
            suggestionsBox.appendChild(link);
        });
        
        suggestionsBox.hidden = false;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});