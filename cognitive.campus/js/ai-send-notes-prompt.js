function sendMessage() {
    const input = document.getElementById('aiNotesPrompt');
    const message = input.value.trim();
    if (!message) return;
    input.value = '';

    // Disable button and show loading state
    const generateButton = document.getElementById('generateAiNotes');
    generateButton.disabled = true;
    generateButton.innerHTML = 'Generating...';

    fetch('notes-handle-prompt.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        if (tinymce.activeEditor) {
            let currentContent = tinymce.activeEditor.getContent();
            
            // Prepare content with text
            let updatedContent = currentContent + '\n\n' + 
                '--- AI-Generated Notes ---\n' + 
                data.text;
            
            // Add image if available
            if (data.image) {
                updatedContent += '\n\n<img src="data:image/png;base64,' + data.image + '" alt="AI Generated Illustration" style="max-width:100%; height:auto;">';
            }
            
            // Set the updated content in the editor
            tinymce.activeEditor.setContent(updatedContent);
            saveContent(updatedContent);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate AI notes. Please try again.');
    })
    .finally(() => {
        // Re-enable button
        generateButton.disabled = false;
        generateButton.innerHTML = 'Generate';
    });
}