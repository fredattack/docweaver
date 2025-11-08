/**
 * Laravel Artifacts - Scripts JavaScript
 *
 * Scripts de base pour le dashboard Laravel Artifacts.
 */

// Auto-dismiss des messages flash après 5 secondes
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert[role="alert"]');

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';

            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Fonction utilitaire pour copier du texte dans le presse-papiers
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    } else {
        // Fallback pour les navigateurs plus anciens
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        return new Promise((resolve, reject) => {
            document.execCommand('copy') ? resolve() : reject();
            textArea.remove();
        });
    }
}

// Fonction pour formater les dates de manière relative
function formatRelativeTime(date) {
    const now = new Date();
    const diff = now - new Date(date);
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `il y a ${days} jour${days > 1 ? 's' : ''}`;
    if (hours > 0) return `il y a ${hours} heure${hours > 1 ? 's' : ''}`;
    if (minutes > 0) return `il y a ${minutes} minute${minutes > 1 ? 's' : ''}`;
    return 'à l\'instant';
}

// Fonction pour afficher des tooltips
function initTooltips() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');

    tooltipTriggers.forEach(trigger => {
        const tooltipText = trigger.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = tooltipText;

        trigger.classList.add('has-tooltip');
        trigger.appendChild(tooltip);
    });
}

// Fonction de confirmation avant suppression
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// Fonction pour activer/désactiver les boutons de chargement
function setLoadingState(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner"></span> Chargement...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || button.innerHTML;
    }
}

// Gestion des filtres de table (recherche en direct)
function setupTableFilters() {
    const searchInputs = document.querySelectorAll('[data-table-search]');

    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);

        if (!table) return;

        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
}

// Fonction pour le tri des colonnes de table
function setupTableSorting() {
    const sortableHeaders = document.querySelectorAll('[data-sortable]');

    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-sortable');
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            const isAscending = this.classList.contains('sort-asc');

            rows.sort((a, b) => {
                const aValue = a.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';
                const bValue = b.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';

                return isAscending
                    ? bValue.localeCompare(aValue)
                    : aValue.localeCompare(bValue);
            });

            // Retirer les classes de tri de tous les headers
            sortableHeaders.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));

            // Ajouter la classe appropriée
            this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');

            // Réorganiser les lignes
            rows.forEach(row => tbody.appendChild(row));
        });
    });
}

// Fonction pour charger dynamiquement du contenu
async function loadContent(url, targetElement) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Erreur de chargement');

        const html = await response.text();
        targetElement.innerHTML = html;
    } catch (error) {
        console.error('Erreur lors du chargement du contenu:', error);
        targetElement.innerHTML = '<p class="text-red-600">Erreur lors du chargement du contenu.</p>';
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    setupTableFilters();
    setupTableSorting();

    // Log pour le débogage
    console.log('Laravel Artifacts Dashboard loaded successfully');
});

// Export des fonctions pour usage global
window.LaravelArtifacts = {
    copyToClipboard,
    formatRelativeTime,
    confirmDelete,
    setLoadingState,
    loadContent
};
