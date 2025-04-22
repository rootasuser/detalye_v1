 document.getElementById('userDropdown').addEventListener('click', function(e) {
    e.preventDefault();
    this.nextElementSibling.classList.toggle('show');
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('#userDropdown') && !e.target.closest('.dropdown-menu')) {
        const dropdown = document.querySelector('.dropdown-menu');
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
});

document.getElementById('userDropdown').addEventListener('focus', function() {
    this.nextElementSibling.classList.add('show');
});