function sendImageMessage() {
    const prompt = document.getElementById('aiNotesPrompt').value.trim();
    if (!prompt) {
        return;
    }

    // Show loading state
    const generateBtn = document.getElementById('generateAiImage');
    const originalText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Generating...';
    generateBtn.disabled = true;

    // Create or get the image container
    let imageContainer = document.getElementById('aiGeneratedImage');
    if (!imageContainer) {
        imageContainer = document.createElement('div');
        imageContainer.id = 'aiGeneratedImage';
        imageContainer.className = 'mt-3';
        document.getElementById('aiNotesContainer').appendChild(imageContainer);
    }

    // Send the request
    fetch('ai-handle-image.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message=${encodeURIComponent(prompt)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create image element
            const img = `<img src="${data.image}" alt="Generated image" style="max-width: 100%; height: auto;">`;
            imageContainer.innerHTML = img;
            
            // Add the image to the TinyMCE editor
            const editor = tinymce.activeEditor;
            if (editor) {
                editor.insertContent(img);
            }
        } else {
            imageContainer.innerHTML = `<div class="alert alert-danger">Error generating image: ${data.error || 'Unknown error'}</div>`;
        }
    })
    .catch(error => {
        imageContainer.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    })
    .finally(() => {
        // Reset button state
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    });
}

// Add event listener to the generate image button
document.getElementById('generateAiImage').onclick = sendImageMessage;