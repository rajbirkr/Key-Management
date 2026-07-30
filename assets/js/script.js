// Simple Beginner JavaScript Helper for Room Selection in floor.php

function selectRoom(roomNumber, category) {
    // Get the room input field and category input field
    var roomInput = document.getElementById('selected_room_input');
    var categoryInput = document.getElementById('selected_category_input');

    if (roomInput && categoryInput) {
        roomInput.value = roomNumber;
        categoryInput.value = category;

        // Visual feedback highlight
        var roomCards = document.querySelectorAll('.room-card');
        roomCards.forEach(function(card) {
            card.classList.remove('selected');
        });

        // Scroll smoothly to form
        roomInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        roomInput.focus();
    }
}
