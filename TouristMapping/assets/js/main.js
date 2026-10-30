
document.addEventListener('DOMContentLoaded', function () {
    const searchBox = document.getElementById('destinationSearch');
    const destinationList = document.getElementById('destinationList');

    if (!searchBox || !destinationList) {
        return;
    }

    searchBox.addEventListener('input', function () {
        const searchText = searchBox.value.toLowerCase().trim();
        const cards = destinationList.querySelectorAll('.destination-card');

        cards.forEach(function (card) {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(searchText) ? '' : 'none';
        });
    });
});
