<script>
    // Speech-to-Text Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sttButton = document.getElementById('sttButton');
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;

            // Event listener for STT button
            sttButton.addEventListener('click', () => {
                if (sttButton.classList.contains('btn-link')) {
                    recognition.start();
                    sttButton.classList.remove('btn-link');
                    sttButton.classList.add('btn-primary');
                    sttButton.querySelector('i').classList.remove('bx-microphone');
                    sttButton.querySelector('i').classList.add('bx-microphone-off');
                    sttButton.setAttribute('data-bs-original-title', 'Stop Listening');
                } else {
                    recognition.stop();
                    sttButton.classList.remove('btn-primary');
                    sttButton.classList.add('btn-link');
                    sttButton.querySelector('i').classList.remove('bx-microphone-off');
                    sttButton.querySelector('i').classList.add('bx-microphone');
                    sttButton.setAttribute('data-bs-original-title', 'Speech to Text');
                }
            });

            // Handle speech recognition results
            recognition.onresult = (event) => {
                const result = event.results[event.results.length - 1];
                const transcript = result[0].transcript;

                if (result.isFinal) {
                    const editor = tinymce.get('blog-editor');
                    editor.setContent(editor.getContent() + ' ' + transcript);
                    saveContent(editor.getContent());
                }
            };

            // Handle speech recognition errors
            recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
            };
        } else {
            sttButton.style.display = 'none';
            console.log('Speech recognition not supported');
        }
    });
</script>