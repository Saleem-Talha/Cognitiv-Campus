<script>
    // Text-to-Speech Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const ttsButton = document.getElementById('ttsButton');
        const readonlyTtsButton = document.getElementById('readonlyTtsButton');
        let speechSynthesis = window.speechSynthesis;

        // Function to get selected text from an element
        function getSelectedText(element) {
            let text = "";
            if (typeof window.getSelection != "undefined") {
                text = window.getSelection().toString();
            } else if (typeof document.selection != "undefined" && document.selection.type == "Text") {
                text = document.selection.createRange().text;
            }
            return text;
        }

        // Function to read text aloud
        function readAloud(content) {
            // Stop any ongoing speech
            speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(content);
            speechSynthesis.speak(utterance);
        }

        // Event listener for TTS button in editable mode
        ttsButton.addEventListener('click', () => {
            const editor = tinymce.get('blog-editor');
            let content = editor.selection.getContent({
                format: 'text'
            });

            if (!content) {
                // If no text is selected, read the entire content
                content = editor.getContent({
                    format: 'text'
                });
            }

            readAloud(content);
        });

        // Event listener for TTS button in readonly mode
        readonlyTtsButton.addEventListener('click', () => {
            const readonlyContent = document.getElementById('readonlyContent');
            let content = getSelectedText(readonlyContent);

            if (!content) {
                // If no text is selected, read the entire content
                content = readonlyContent.textContent;
            }

            readAloud(content);
        });
    });
</script>