<script src="axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentForm = document.getElementById('studentForm');
        const originalData = new FormData(studentForm);

        document.getElementById('revertChanges').addEventListener('click', function(e) {
            e.preventDefault();
            for (let [key, value] of originalData.entries()) {
                if (studentForm.elements[key]) {
                    studentForm.elements[key].value = value;
                }
            }
        });

        studentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            axios.post('editstudent.php', formData)
                .then(response => {
                    if (response.data.success) {
                        alert('Student edited successfully');
                        window.location.href = 'students.php';
                    } else {
                        alert('Failed to edit student: ' + response.data.error);
                    }
                })
                .catch(error => {
                    console.error('There was an error!', error);
                });
        });
    });
</script>

<form id="studentForm">
    <button type="submit">Save Changes</button>
    <button id="revertChanges">Revert Changes</button>
</form>