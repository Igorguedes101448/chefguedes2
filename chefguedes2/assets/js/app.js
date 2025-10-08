// assets/js/app.js - ChefGuedes JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('ChefGuedes app loaded');
    
    // Initialize components
    initSearch();
    initStarRating();
    initComments();
    initToasts();
    initHeartbeat();
});

// Search functionality
function initSearch() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }
}

function handleSearch(e) {
    const query = e.target.value;
    console.log('Searching for:', query);
    // TODO: Implement search API call
}

// Star rating system
function initStarRating() {
    const starContainers = document.querySelectorAll('.star-rating');
    starContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        loadUserRating(container);
        
        stars.forEach((star, index) => {
            star.addEventListener('click', () => setRating(container, index + 1));
            star.addEventListener('mouseover', () => highlightStars(container, index + 1));
        });
        
        container.addEventListener('mouseleave', () => {
            const rating = container.dataset.rating || 0;
            highlightStars(container, rating);
        });
    });
}

function setRating(container, rating) {
    container.dataset.rating = rating;
    highlightStars(container, rating);
    
    const recipeId = container.dataset.recipeId;
    if (recipeId) {
        submitRating(recipeId, rating);
    }
}

function highlightStars(container, count) {
    const stars = container.querySelectorAll('.star');
    stars.forEach((star, index) => {
        star.classList.toggle('active', index < count);
    });
}

function loadUserRating(container) {
    const recipeId = container.dataset.recipeId;
    if (!recipeId) return;
    
    fetch(`/chefguedes2/api/ratings.php?recipe_id=${recipeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user_rating) {
                container.dataset.rating = data.user_rating;
                highlightStars(container, data.user_rating);
            }
        })
        .catch(err => console.error('Error loading rating:', err));
}

function submitRating(recipeId, stars) {
    const csrfToken = getCsrfToken();
    
    fetch('/chefguedes2/api/ratings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            recipe_id: recipeId,
            stars: stars,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Avaliação registada!', 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error submitting rating:', err);
        showToast('Erro ao registar avaliação', 'error');
    });
}

// Comments system
function initComments() {
    const commentsList = document.getElementById('comments-list');
    if (commentsList) {
        loadComments(commentsList.dataset.recipeId);
    }
    
    const commentForm = document.querySelector('.comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', handleCommentSubmit);
    }
}

function loadComments(recipeId) {
    fetch(`/chefguedes2/api/comments.php?recipe_id=${recipeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayComments(data.comments);
            }
        })
        .catch(err => console.error('Error loading comments:', err));
}

function displayComments(comments) {
    const commentsList = document.getElementById('comments-list');
    if (!commentsList) return;
    
    commentsList.innerHTML = comments.map(comment => `
        <div class="comment" data-comment-id="${comment.id}">
            <div class="comment-header">
                <span class="comment-author">${escapeHtml(comment.author_name)}</span>
                <span class="comment-date">${formatDate(comment.created_at)}</span>
            </div>
            <div class="comment-content">${escapeHtml(comment.content)}</div>
            <div class="comment-actions">
                <button onclick="reportComment(${comment.id})">Reportar</button>
                ${comment.user_id == getCurrentUserId() ? `<button onclick="deleteComment(${comment.id})">Eliminar</button>` : ''}
            </div>
        </div>
    `).join('');
}

function handleCommentSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const content = form.querySelector('textarea[name="content"]').value;
    const recipeId = form.dataset.recipeId;
    const csrfToken = form.querySelector('input[name="csrf_token"]')?.value || getCsrfToken();
    
    if (!content.trim()) {
        showToast('Por favor escreva um comentário', 'error');
        return;
    }
    
    fetch('/chefguedes2/api/comments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            recipe_id: recipeId,
            content: content,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Comentário adicionado!', 'success');
            form.reset();
            loadComments(recipeId);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error submitting comment:', err);
        showToast('Erro ao adicionar comentário', 'error');
    });
}

function deleteComment(commentId) {
    if (!confirm('Tem certeza que quer eliminar este comentário?')) return;
    
    fetch('/chefguedes2/api/comments.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: commentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Comentário eliminado', 'success');
            const recipeId = document.getElementById('comments-list').dataset.recipeId;
            loadComments(recipeId);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error deleting comment:', err);
        showToast('Erro ao eliminar comentário', 'error');
    });
}

function reportComment(commentId) {
    if (!confirm('Reportar este comentário?')) return;
    
    fetch('/chefguedes2/api/comments.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: commentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Comentário reportado', 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error reporting comment:', err);
        showToast('Erro ao reportar comentário', 'error');
    });
}

// Toast notifications
function initToasts() {
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };
}

// Online status heartbeat
function initHeartbeat() {
    if (document.body.dataset.userId) {
        setInterval(sendHeartbeat, 60000); // Every minute
    }
}

function sendHeartbeat() {
    fetch('/chefguedes2/api/heartbeat.php', {
        method: 'POST',
        credentials: 'same-origin'
    }).catch(err => console.log('Heartbeat failed:', err));
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-PT') + ' ' + date.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
}

function getCurrentUserId() {
    return document.body.dataset.userId || null;
}

function getCsrfToken() {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken && metaToken.content) {
        return metaToken.content;
    }

    const hiddenField = document.querySelector('input[name="csrf_token"]');
    if (hiddenField && hiddenField.value) {
        return hiddenField.value;
    }

    return '';
}

// Form validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'Este campo é obrigatório');
            isValid = false;
        } else {
            clearFieldError(input);
        }
    });
    
    return isValid;
}

function showFieldError(input, message) {
    clearFieldError(input);
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    input.parentNode.appendChild(error);
    input.classList.add('error');
}

function clearFieldError(input) {
    const error = input.parentNode.querySelector('.field-error');
    if (error) error.remove();
    input.classList.remove('error');
}