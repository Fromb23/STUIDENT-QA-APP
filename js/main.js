document.addEventListener("DOMContentLoaded", function () {
    initLucideIcons();
    handleReplyForm();
    handleReplySubmission();
    handleActions();
    handleParentResponse();
    handleReplyToggle();
});

// Initialize Lucide icons
function initLucideIcons() {
    lucide.createIcons();
}

// Toggle dropdown menu
function toggleDropdown() {
    const dropdown = document.getElementById("dropdown");
    dropdown.classList.toggle("hidden");
}

// Close dropdown when clicking outside
document.addEventListener("click", function (event) {
    const dropdown = document.getElementById("dropdown");
    const button = document.getElementById("dropdown-button");

    if (!dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.add("hidden");
    }
});

// Show/Hide reply form on double click (only one open at a time)
function handleReplyForm() {
    document.querySelectorAll(".response-item").forEach((item) => {
        item.addEventListener("dblclick", () => {
            const responseId = item.getAttribute("data-id");
            const replyForm = document.querySelector(`.response-reply-form[data-id="${responseId}"]`);

            document.querySelectorAll(".response-reply-form").forEach((form) => {
                if (form !== replyForm) {
                    form.classList.add("hidden");
                }
            });

            if (replyForm) replyForm.classList.toggle("hidden");
        });
    });
}

// Submit a reply
function handleReplySubmission() {
    document.querySelectorAll(".send-reply-btn").forEach((button) => {
        button.addEventListener("click", () => {
            const responseId = button.getAttribute("data-id");
            const replyInput = document.querySelector(`.response-reply-form[data-id="${responseId}"] input`);
            const replyContent = replyInput.value.trim();

            if (replyContent) {
                fetch("processes/submit_response.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({
                        question_id: document.querySelector("input[name='question_id']").value,
                        content: replyContent,
                        parent_response_id: responseId,
                    }),
                })
                    .then(response => response.text())
                    .then(() => location.reload())
                    .catch(error => console.error("Error:", error));
            }
        });
    });
}

// Handle Like, Edit, Delete, Share actions
function handleActions() {
    document.querySelectorAll(".like-btn, .edit-btn, .delete-btn, .share-btn").forEach((button) => {
        button.addEventListener("click", () => {
            const action = button.classList.contains("like-btn") ? "like" :
                          button.classList.contains("edit-btn") ? "edit" :
                          button.classList.contains("delete-btn") ? "delete" : "share";
            const responseId = button.getAttribute("data-id");

            if (action === "delete" && !confirm("Are you sure you want to delete this response?")) return;

            fetch(`processes/${action}_response.php`, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ response_id: responseId }),
            })
                .then(response => response.text())
                .then(() => action === "delete" ? location.reload() : alert(`${action} action successful!`))
                .catch(error => console.error("Error:", error));
        });
    });
}

// Set parent response ID when replying
function handleParentResponse() {
    const parentResponseInput = document.getElementById("parent_response_id");
    window.setParentResponseId = function (responseId) {
        if (parentResponseInput) {
            parentResponseInput.value = responseId;
            document.getElementById("response-form").scrollIntoView({ behavior: "smooth" });
        }
    };
}

function handleReplyToggle() {
    const openThreads = new Set(JSON.parse(localStorage.getItem("openThreads")) || []);

    document.querySelectorAll(".toggle-thread").forEach(button => {
        const responseId = button.getAttribute("data-id");
        let thread = document.getElementById(`thread-${responseId}`);

        // Restore thread state on page load
        if (openThreads.has(responseId) && thread) {
            thread.style.display = "block";
            button.innerHTML = "Hide Replies ▲";
        }

        button.addEventListener("click", function () {
            if (thread.style.display === "none") {
                thread.style.display = "block";
                button.innerHTML = "Hide Replies ▲";
                openThreads.add(responseId);
            } else {
                thread.style.display = "none";
                button.innerHTML = "View Replies ▼";
                openThreads.delete(responseId);
            }
            
            localStorage.setItem("openThreads", JSON.stringify([...openThreads]));
        });
    });
}

// Handle logout
document.addEventListener("DOMContentLoaded", function () {
    const logoutLink = document.querySelector("a[href='public/logout.php']");

    if (logoutLink) {
        logoutLink.addEventListener("click", function (event) {
            event.preventDefault();
            localStorage.clear();
            window.location.href = "public/logout.php";
        });
    }
});

function toggleSubcribeButton() {
    const emailInput = document.getElementById("email");
    const subscribeButton = document.getElementById("subscribeButton");

    if (emailInput.value.trim() === "") {
        subscribeButton.disabled = true;
    }
    else {
        subscribeButton.disabled = false;
    }
}