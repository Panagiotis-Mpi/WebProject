<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit();
}

$thesis_id = $_GET['id'] ?? null;
?>
<h1>Λεπτομέρειες Διπλωματικής</h1>
<div id="thesis-details-container">
    <p>Φόρτωση λεπτομερειών...</p>
</div>
<p><a href="view_theses.php">← Επιστροφή στις Διπλωματικές</a></p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const thesisId = <?= json_encode($thesis_id); ?>;
    const detailsContainer = document.getElementById('thesis-details-container');

    if (!thesisId) {
        detailsContainer.innerHTML = '<p style="color:red;">Δεν βρέθηκε ID διπλωματικής.</p>';
        return;
    }

    fetch(`../api/get_thesis_details_secretary_api.php?id=${thesisId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                detailsContainer.innerHTML = `<p style="color:red;">Σφάλμα: ${data.error}</p>`;
                return;
            }

            const thesis = data.thesis;
            const committee = data.committee;
            
            let detailsHtml = `
                <p><strong>Τίτλος:</strong> ${thesis.title}</p>
                <p><strong>Περιγραφή:</strong> ${thesis.summary}</p>
                <p><strong>Φοιτητής:</strong> ${thesis.student_first} ${thesis.student_last}</p>
                <p><strong>Επιβλέπων:</strong> ${thesis.sup_first} ${thesis.sup_last}</p>
                <p><strong>Κατάσταση:</strong> ${thesis.status}</p>
                <p><strong>Ανάθεση:</strong> ${thesis.assignment_date ? new Date(thesis.assignment_date).toLocaleString() : 'Δεν έχει ανατεθεί'}</p>
                <p><strong>Χρόνος από Ανάθεση:</strong> ${thesis.time_since_assignment}</p>
                
                <h4>Τριμελής Επιτροπή</h4>
            `;

            if (committee.length > 0) {
                detailsHtml += '<ul>';
                committee.forEach(member => {
                    detailsHtml += `<li>${member.first_name} ${member.last_name} (${member.role === 'supervisor' ? 'Επιβλέπων' : 'Μέλος'})</li>`;
                });
                detailsHtml += '</ul>';
            } else {
                detailsHtml += '<p>Δεν έχει οριστεί επιτροπή.</p>';
            }

            detailsContainer.innerHTML = detailsHtml;
        })
        .catch(error => {
            detailsContainer.innerHTML = `<p style="color:red;">Σφάλμα φόρτωσης: ${error.message}</p>`;
            console.error('Fetch error:', error);
        });
});
</script>