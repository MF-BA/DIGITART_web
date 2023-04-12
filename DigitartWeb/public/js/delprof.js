document.addEventListener('DOMContentLoaded', function() {
    var deleteBtn = document.getElementById('delete-btn');
    deleteBtn.addEventListener('click', function(event) {
        var confirmDelete = confirm("Are you sure you want to delete your account?");
        if (!confirmDelete) {
            event.preventDefault();
        }
    });
});