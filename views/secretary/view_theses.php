<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit();
}
?>
<h1>Ενεργές & Υπό Εξέταση Διπλωματικές</h1>
<div id="loading-message">Φόρτωση...</div>
<table border="1" cellpadding="8" style="display:none;">
    <thead>
        <tr>
            <th>Τίτλος</th>
            <th>Φοιτητής</th>
            <th>Κατάσταση</th>
            <th>Ενέργειες</th>
        </tr>
    </thead>
    <tbody id="theses-table-body">
    </tbody>
</table>
<div id="error-message" style="color:red; display:none;">Σφάλμα κατά την φόρτωση των διπλωματικών.</div>
<p><a href="dashboard.php">← Επιστροφή</a></p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('theses-table-body');
    const loadingMessage = document.getElementById('loading-message');
    const errorMessage = document.getElementById('error-message');
    const table = document.querySelector('table');

    fetch('../api/get_active_theses_api.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(theses => {
            loadingMessage.style.display = 'none';
            if (theses.error) {
                errorMessage.textContent = `Σφάλμα API: ${theses.error}`;
                errorMessage.style.display = 'block';
                return;
            }
            if (theses.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4">Δεν υπάρχουν ενεργές ή υπό εξέταση διπλωματικές.</td></tr>';
            } else {
                theses.forEach(thesis => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${thesis.title}</td>
                        <td>${thesis.first_name} ${thesis.last_name}</td>
                        <td>${thesis.status}</td>
                        <td><a href="view_thesis_details.php?id=${thesis.id}">Προβολή</a></td>
                    `;
                    tableBody.appendChild(row);
                });
                table.style.display = 'table';
            }
        })
        .catch(error => {
            loadingMessage.style.display = 'none';
            errorMessage.textContent = `Σφάλμα φόρτωσης: ${error.message}`;
            errorMessage.style.display = 'block';
        });
});
</script>