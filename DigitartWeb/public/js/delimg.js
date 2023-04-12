document.addEventListener('DOMContentLoaded', function() {
    var deleteimgBtn = document.getElementById('delete-img-btn');
    deleteimgBtn.addEventListener('click', function(event) {
        var confirmDelete = confirm("Are you sure you want to delete this item?");
        if (!confirmDelete) {
            event.preventDefault();
        }
    });
});