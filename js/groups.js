function makeEditable() {
    document.getElementById('descriptionText').style.display = 'none';
    document.getElementById('descriptionEditor').style.display = 'block';

    document.getElementById('descriptionInput').focus();
}

document.getElementById('descriptionText').addEventListener('dblclick', makeEditable);

function closeEditorIfClickedOutside(event) {
    var descriptionEditor = document.getElementById('descriptionEditor');
    var descriptionText = document.getElementById('descriptionText');

    if (!descriptionEditor.contains(event.target) && !descriptionText.contains(event.target)) {
        document.getElementById('descriptionText').style.display = 'inline';
        document.getElementById('descriptionEditor').style.display = 'none';
    }
}

function updateDescription() {
    var description = document.getElementById('descriptionInput').value.trim();

    if (description !== '') {
        // Get groupId from the data attribute
        var groupId = document.getElementById('groupData').getAttribute('data-group-id');

        var formData = new FormData();
        formData.append('group_id', groupId);
        formData.append('description', description);
        // Debugging: log FormData content
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        fetch('../processes/groups.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log('Raw response:', data);
        
            try {
                const jsonData = JSON.parse(data);  // Attempt to parse as JSON
                if (jsonData.success) {
                    document.getElementById('descriptionText').textContent = description;
                    document.getElementById('descriptionText').style.display = 'inline';
                    document.getElementById('descriptionEditor').style.display = 'none';
                } else {
                    alert('Failed to update description: ' + jsonData.message);
                }
            } catch (error) {
                console.error('Error parsing JSON:', error);
                alert('There was an error updating the description.');
            }
        })
        .catch(error => {
            console.error('Error updating description:', error);
        });
    }
    else {
        alert('Description cannot be empty.');
    }
}  

document.addEventListener('click', closeEditorIfClickedOutside);

document.getElementById('descriptionInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        updateDescription();
    }
});