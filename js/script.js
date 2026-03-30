// Search input live filter on properties page
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const value = this.value.toLowerCase();
        const cards = document.querySelectorAll('.card');

        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(value) ? '' : 'none';
        });
    });
}

// Confirm delete buttons
document.querySelectorAll('.btn-danger').forEach(button => {
    button.addEventListener('click', function (e) {
        const ok = confirm('Are you sure you want to delete this property?');
        if (!ok) {
            e.preventDefault();
        }
    });
});

// Add property form validation
const addPropertyForm = document.querySelector('form[action="add_property.php"]');
if (addPropertyForm) {
    addPropertyForm.addEventListener('submit', function (e) {
        const title = document.querySelector('input[name="title"]')?.value.trim();
        const location = document.querySelector('input[name="location"]')?.value.trim();
        const price = document.querySelector('input[name="price"]')?.value.trim();

        if (!title || !location || !price) {
            alert('Please fill all required fields.');
            e.preventDefault();
            return;
        }

        if (Number(price) <= 0) {
            alert('Price must be greater than 0.');
            e.preventDefault();
        }
    });
}