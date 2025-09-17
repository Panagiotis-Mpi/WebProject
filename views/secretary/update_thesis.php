<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit();
}
?>
<h1>Ενημέρωση Κατάστασης Διπλωματικής</h1>
<div id="message"></div>

<form id="update-form">
    <label>Επιλέξτε διπλωματική:</label>
    <select name="thesis_id" id="thesis-select" required>
        <option value="">Φόρτωση...</option>
    </select>
    <br><br>
    
    <label>Νέα Κατάσταση:</label>
    <select name="status" id="status-select" required>
        <option value="completed">Περατωμένη</option>
        <option value="cancelled">Ακυρωμένη</option>
        <option value="active">Ενεργή (εφόσον εγκριθεί από Γ.Σ.)</option>
    </select>
    <br><br>

    <div id="active-fields" style="display:none;">
        <label>Αριθμός/Έτος ΓΣ (Ενεργή):</label>
        <input type="text" name="gs_number_active" id="gs_number_active">
    </div>

    <div id="cancelled-fields" style="display:none;">
        <label>Αριθμός/Έτος ΓΣ (Ακύρωση):</label>
        <input type="text" name="gs_number_cancelled" id="gs_number_cancelled">
        <label>Λόγος Ακύρωσης:</label>
        <textarea name="cancellation_reason" id="cancellation_reason"></textarea>
    </div>

    <div id="completed-fields" style="display:none;">
        <label>Βαθμός:</label>
        <input type="number" name="grade" id="grade" min="0" max="10">
    </div>

    <br>
    <button type="submit">Ενημέρωση</button>
</form>

<p><a href="dashboard.php">← Επιστροφή</a></p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const thesisSelect = document.getElementById('thesis-select');
    const statusSelect = document.getElementById('status-select');
    const updateForm = document.getElementById('update-form');
    const messageDiv = document.getElementById('message');
    const activeFields = document.getElementById('active-fields');
    const cancelledFields = document.getElementById('cancelled-fields');
    const completedFields = document.getElementById('completed-fields');

    // Φόρτωση των διπλωματικών
    fetch('../api/get_all_theses_api.php')
        .then(response => response.json())
        .then(theses => {
            thesisSelect.innerHTML = '<option value="">Επιλέξτε...</option>';
            if (theses.error) {
                messageDiv.innerHTML = `<p style="color:red;">Σφάλμα φόρτωσης: ${theses.error}</p>`;
                return;
            }
            theses.forEach(thesis => {
                const option = document.createElement('option');
                option.value = thesis.id;
                option.textContent = `${thesis.title} (${thesis.first_name} ${thesis.last_name}) [${thesis.status}]`;
                thesisSelect.appendChild(option);
            });
        })
        .catch(error => {
            messageDiv.innerHTML = `<p style="color:red;">Σφάλμα φόρτωσης: ${error.message}</p>`;
        });

    // Εμφάνιση/Απόκρυψη πεδίων ανάλογα με την κατάσταση
    statusSelect.addEventListener('change', function() {
        activeFields.style.display = this.value === 'active' ? 'block' : 'none';
        cancelledFields.style.display = this.value === 'cancelled' ? 'block' : 'none';
        completedFields.style.display = this.value === 'completed' ? 'block' : 'none';
    });

    // Χειρισμός υποβολής φόρμας
    updateForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch('update_thesis_logic.php', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                messageDiv.innerHTML = `<p style="color:green;">${result.message}</p>`;
                // Ανανέωση της λίστας
                fetch('../api/get_all_theses_api.php')
                    .then(res => res.json())
                    .then(theses => {
                        thesisSelect.innerHTML = '<option value="">Επιλέξτε...</option>';
                        theses.forEach(thesis => {
                            const option = document.createElement('option');
                            option.value = thesis.id;
                            option.textContent = `${thesis.title} (${thesis.first_name} ${thesis.last_name}) [${thesis.status}]`;
                            thesisSelect.appendChild(option);
                        });
                    });
            } else {
                messageDiv.innerHTML = `<p style="color:red;">${result.error}</p>`;
            }
        })
        .catch(error => {
            messageDiv.innerHTML = `<p style="color:red;">Σφάλμα αποστολής: ${error.message}</p>`;
        });
    });
});
</script>