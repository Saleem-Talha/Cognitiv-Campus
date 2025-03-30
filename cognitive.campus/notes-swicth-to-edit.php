<script>
    // Edit Mode Switch Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const editSwitch = document.getElementById('editSwitch');
        const readonlyDiv = document.querySelector('.readonly');
        const editorDiv = document.querySelector('.editor');
        const textarea = document.getElementById('blog-editor');
        let clickCount = 0;

        editSwitch.addEventListener('change', function() {
            if (this.checked) {
                readonlyDiv.classList.add('d-none');
                editorDiv.classList.remove('d-none'); 
                clickCount++;

                // Remove any existing TinyMCE instance
                tinymce.remove('#blog-editor');

                // Initialize TinyMCE with different configurations based on click count
                if (clickCount === 1 || clickCount === 2) {
                    initTinyMCE();
                }

                if (clickCount === 2) {
                    clickCount = 0; // Reset click count after second click
                }
            } else {
                // Reload the page when switching to readonly mode
                location.reload();
            }
        });
    });
</script>