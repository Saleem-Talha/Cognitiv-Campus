document.addEventListener('DOMContentLoaded', function() {
    const typeFilter = document.getElementById('typeFilter');
    const notesList = document.getElementById('notesList');

    typeFilter.addEventListener('change', function() {
        const selectedType = this.value.toLowerCase();
        const notes = notesList.getElementsByClassName('note-item');

        for (let note of notes) {
            const noteType = note.getAttribute('data-type').toLowerCase();
            if (selectedType === 'all' || noteType === selectedType) {
                note.classList.remove('d-none');
            } else {
                note.classList.add('d-none');
            }
        }
    });
});