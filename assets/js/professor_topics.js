document.addEventListener('DOMContentLoaded', function() {
    const topicsList = document.getElementById('topics-list');
    const modal = document.getElementById('topic-modal');
    const topicForm = document.getElementById('topic-form');
    const closeModal = document.getElementById('close-modal');

    // Φόρτωση θεμάτων AJAX
    fetch('../../api/professor/get_professor_topics.php')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                data.topics.forEach(topic => {
                    const div = document.createElement('div');
                    div.innerHTML = `<a href="#" class="topic-link" data-id="${topic.id}" 
                                     data-title="${topic.title}" data-summary="${topic.summary}" data-pdf="${topic.pdf_path}">
                                     ${topic.title}</a>`;
                    topicsList.appendChild(div);
                });

                // Προσθήκη click listener σε κάθε link
                document.querySelectorAll('.topic-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('topic-id').value = this.dataset.id;
                        document.getElementById('topic-title').value = this.dataset.title;
                        document.getElementById('topic-summary').value = this.dataset.summary;
                        document.getElementById('topic-pdf').value = this.dataset.pdf;
                        modal.style.display = 'block';
                    });
                });
            }
        });

    // Κλείσιμο modal
    closeModal.addEventListener('click', () => modal.style.display = 'none');

    // Αποθήκευση αλλαγών
    topicForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(topicForm);

        fetch('../../api/professor/update_topic.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if(data.success) {
                modal.style.display = 'none';
                location.reload(); // Φορτώνει ξανά τα θέματα
            }
        });
    });
});
