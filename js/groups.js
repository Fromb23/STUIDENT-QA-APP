function makeEditable() {
  document.getElementById("descriptionText").style.display = "none";
  document.getElementById("descriptionEditor").style.display = "block";

  document.getElementById("descriptionInput").focus();
}

document
  .getElementById("descriptionText")
  .addEventListener("dblclick", makeEditable);

function closeEditorIfClickedOutside(event) {
  var descriptionEditor = document.getElementById("descriptionEditor");
  var descriptionText = document.getElementById("descriptionText");

  if (
    !descriptionEditor.contains(event.target) &&
    !descriptionText.contains(event.target)
  ) {
    document.getElementById("descriptionText").style.display = "inline";
    document.getElementById("descriptionEditor").style.display = "none";
  }
}

function updateDescription() {
  var description = document.getElementById("descriptionInput").value.trim();

  if (description !== "") {
    var groupId = document
      .getElementById("groupData")
      .getAttribute("data-group-id");

    var formData = new FormData();
    formData.append("group_id", groupId);
    formData.append("description", description);

    fetch("../processes/groups.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((data) => {
        try {
          const jsonData = JSON.parse(data);
          if (jsonData.success) {
            document.getElementById("descriptionText").textContent =
              description;
            document.getElementById("descriptionText").style.display = "inline";
            document.getElementById("descriptionEditor").style.display = "none";
          } else {
            alert("Failed to update description: " + jsonData.message);
          }
        } catch (error) {
          alert("There was an error updating the description.");
        }
      })
      .catch((error) => {
        alert("something went wrong", error);
      });
  } else {
    alert("Description cannot be empty.");
  }
}

document.addEventListener("click", closeEditorIfClickedOutside);

document
  .getElementById("descriptionInput")
  .addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      updateDescription();
    }
  });

$(document).ready(function () {
  $("#message").emojioneArea({
    pickerPosition: "top",
    tones: false,
    buttonTitle: "Pick an emoji",
  });

  $("#emojiButton").click(function () {
    $("#message").emojioneArea().toggle();
  });
});
