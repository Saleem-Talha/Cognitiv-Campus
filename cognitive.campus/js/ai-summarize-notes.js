

    document.addEventListener('DOMContentLoaded', function() {
    const summarizeNotesBtn = document.getElementById('summarizeNotesBtn');
    const summarizeNotesContainer = document.getElementById('summarizeNotesContainer');
    const summarizedContent = document.getElementById('summarizedContent');
    const summaryLoadingSpinner = document.getElementById('summaryLoadingSpinner');

    summarizeNotesBtn.addEventListener('click', function() {
        // Disable the button to prevent multiple clicks
        summarizeNotesBtn.disabled = true;

        // Reset previous state
        summarizeNotesContainer.classList.add('d-none');
        summarizedContent.textContent = '';

        // Show loading spinner
        summaryLoadingSpinner.classList.remove('d-none');

        // Get the current content from TinyMCE
        if (tinymce.activeEditor) {
            const content = tinymce.activeEditor.getContent({format: 'text'});
            
            // Send content for summarization
            fetch('notes-handle-summarize.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'content=' + encodeURIComponent(content)
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading spinner
                summaryLoadingSpinner.classList.add('d-none');

                // Handle different status scenarios
                switch(data.status) {
                    case 'success':
                        summarizedContent.textContent = data.summary;
                        summarizedContent.className = 'alert alert-primary';
                        break;
                    case 'no_content':
                        summarizedContent.textContent = 'No meaningful content found to summarize.';
                        summarizedContent.className = 'alert alert-primary';
                        break;
                    case 'no_summary':
                        summarizedContent.textContent = 'Unable to generate a useful summary.';
                        summarizedContent.className = 'alert alert-primary';
                        break;
                    default:
                        summarizedContent.textContent = 'Failed to generate summary: ' + data.summary;
                        summarizedContent.className = 'alert alert-danger';
                }
                
                // Show the result container
                summarizeNotesContainer.classList.remove('d-none');
            })
            .catch(error => {
                // Hide loading spinner
                summaryLoadingSpinner.classList.add('d-none');

                console.error('Error:', error);
                summarizedContent.textContent = 'An error occurred during summarization.';
                summarizedContent.className = 'alert alert-danger';
                summarizeNotesContainer.classList.remove('d-none');
            })
            .finally(() => {
                // Re-enable the button
                summarizeNotesBtn.disabled = false;
            });
        } else {
            // Hide loading spinner
            summaryLoadingSpinner.classList.add('d-none');
            
            // Show error if TinyMCE editor not found
            summarizedContent.textContent = 'Editor not available.';
            summarizedContent.className = 'alert alert-danger';
            summarizeNotesContainer.classList.remove('d-none');
            summarizeNotesBtn.disabled = false;
        }
    });
});
