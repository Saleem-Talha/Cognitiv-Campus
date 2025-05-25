<script>
// Global variables to store session information
let editSessionData = {
    isInEditMode: false,
    startTime: null,
    noteId: <?php echo $note_id; ?> // Get the note ID from PHP
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Edit Session Tracker initialized');
    console.log('Note ID:', editSessionData.noteId);
    
    const editSwitch = document.getElementById('editSwitch');
    
    if (!editSwitch) {
        console.error('Edit switch element not found!');
        return;
    }
    
    // Setup edit mode toggle functionality (original behavior)
    editSwitch.addEventListener('change', function() {
        const readonlyDiv = document.querySelector('.readonly');
        const editorDiv = document.querySelector('.editor');
        
        if (this.checked) {
            console.log('Entering edit mode');
            readonlyDiv.classList.add('d-none');
            editorDiv.classList.remove('d-none');
            
            // Start tracking edit session if not already tracking
            if (!editSessionData.isInEditMode) {
                startEditSession();
            }
        } else {
            console.log('Exiting edit mode');
            readonlyDiv.classList.remove('d-none');
            editorDiv.classList.add('d-none');
            
            // End the edit session
            if (editSessionData.isInEditMode) {
                endEditSession();
            }
        }
    });
    
    // Handle user leaving the page without toggling the switch
    window.addEventListener('beforeunload', function(e) {
        if (editSessionData.isInEditMode) {
            console.log('User leaving page while in edit mode');
            endEditSession();
        }
    });
});

/**
 * Start tracking an edit session
 */
function startEditSession() {
    editSessionData.isInEditMode = true;
    editSessionData.startTime = new Date();
    
    console.log('Edit session started at:', editSessionData.startTime);
    console.log('Edit session data:', JSON.stringify(editSessionData));
    
    // Store the start time in session storage as a backup
    sessionStorage.setItem('editSessionStart', editSessionData.startTime.toISOString());
    sessionStorage.setItem('editSessionNoteId', editSessionData.noteId);
}

/**
 * End tracking an edit session and save the data
 */
function endEditSession() {
    if (!editSessionData.isInEditMode) {
        console.log('No active edit session to end');
        return;
    }
    
    const endTime = new Date();
    const durationSeconds = Math.round((endTime - editSessionData.startTime) / 1000);
    
    console.log('Edit session ended at:', endTime);
    console.log('Session duration (seconds):', durationSeconds);
    
    // Prepare data for sending to server
    const sessionData = {
        noteId: editSessionData.noteId,
        startTime: editSessionData.startTime.toISOString(),
        endTime: endTime.toISOString(),
        durationSeconds: durationSeconds
    };
    
    console.log('Sending session data to server:', JSON.stringify(sessionData));
    
    // Send data to server using jQuery AJAX (since jQuery is already loaded on the page)
    saveEditSession(sessionData);
    
    // Reset session data
    editSessionData.isInEditMode = false;
    editSessionData.startTime = null;
    
    // Clear session storage
    sessionStorage.removeItem('editSessionStart');
    sessionStorage.removeItem('editSessionNoteId');
}

/**
 * Save edit session data to the server
 * @param {Object} sessionData - The edit session data to save
 */
function saveEditSession(sessionData) {
    console.log('Saving edit session with data:', JSON.stringify(sessionData));
    
    // Use jQuery AJAX since it's already included in the page
    $.ajax({
        url: 'notes-save-edit-session.php',
        type: 'POST',
        data: sessionData,
        dataType: 'json',
        success: function(response) {
            console.log('Edit session saved successfully:', response);
        },
        error: function(xhr, status, error) {
            console.error('Error saving edit session:', error);
            console.error('Server response:', xhr.responseText);
        }
    });
}
</script>