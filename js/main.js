document.addEventListener("DOMContentLoaded", function () {
  initLucideIcons();
  handleReplyForm();
  handleReplySubmission();
  handleActions();
  handleParentResponse();
  handleReplyToggle();
  setupNavigation();
});

function initLucideIcons() {
  lucide.createIcons();
}

function toggleDropdown() {
  const dropdown = document.getElementById("dropdown");
  dropdown.classList.toggle("hidden");
}

document.addEventListener("click", function (event) {
  const dropdown = document.getElementById("dropdown");
  const button = document.getElementById("dropdown-button");

  if (!dropdown.contains(event.target) && !button.contains(event.target)) {
    dropdown.classList.add("hidden");
  }
});

function handleReplyForm() {
  document.querySelectorAll(".response-item").forEach((item) => {
    item.addEventListener("dblclick", () => {
      const responseId = item.getAttribute("data-id");
      const replyForm = document.querySelector(
        `.response-reply-form[data-id="${responseId}"]`
      );

      document.querySelectorAll(".response-reply-form").forEach((form) => {
        if (form !== replyForm) form.classList.add("hidden");
      });

      if (replyForm) replyForm.classList.toggle("hidden");
    });
  });
}

function handleReplySubmission() {
  document.querySelectorAll(".send-reply-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const responseId = button.getAttribute("data-id");
      const replyInput = document.querySelector(
        `.response-reply-form[data-id="${responseId}"] input`
      );
      const replyContent = replyInput.value.trim();

      if (replyContent) {
        fetch("processes/submit_response.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            question_id: document.querySelector("input[name='question_id']")
              .value,
            content: replyContent,
            parent_response_id: responseId,
          }),
        })
          .then((response) => response.text())
          .then(() => location.reload())
          .catch((error) => console.error("Error:", error));
      }
    });
  });
}

function fetchUpdatedResponses() {
  const questionId = document.querySelector("input[name='question_id']").value;
  fetch(`processes/get_responses.php?question_id=${questionId}`)
    .then((response) => response.text())
    .then((html) => {
      document.getElementById("response-list").innerHTML = html;
      handleReplyForm();
      handleReplyToggle();
    });
}

function handleActions() {
  document
    .querySelectorAll(".like-btn, .edit-btn, .delete-btn, .share-btn")
    .forEach((button) => {
      button.addEventListener("click", () => {
        const action = button.classList.contains("like-btn")
          ? "like"
          : button.classList.contains("edit-btn")
          ? "edit"
          : button.classList.contains("delete-btn")
          ? "delete"
          : "share";
        const responseId = button.getAttribute("data-id");

        if (
          action === "delete" &&
          !confirm("Are you sure you want to delete this response?")
        )
          return;

        fetch(`processes/${action}_response.php`, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ response_id: responseId }),
        })
          .then((response) => response.text())
          .then(() =>
            action === "delete"
              ? location.reload()
              : alert(`${action} action successful!`)
          )
          .catch((error) => console.error("Error:", error));
      });
    });
}

function handleParentResponse() {
  const parentResponseInput = document.getElementById("parent_response_id");
  window.setParentResponseId = function (responseId) {
    if (parentResponseInput) {
      parentResponseInput.value = responseId;
      document
        .getElementById("response-form")
        .scrollIntoView({ behavior: "smooth" });
    }
  };
}

function handleReplyToggle() {
  const openThreads = new Set(
    JSON.parse(localStorage.getItem("openThreads")) || []
  );

  document.querySelectorAll(".toggle-thread").forEach((button) => {
    const responseId = button.getAttribute("data-id");
    let thread = document.getElementById(`thread-${responseId}`);

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
  subscribeButton.disabled = emailInput.value.trim() === "";
}

function setupNavigation() {
  const mainContent = document.getElementById("main-content");
  if (!mainContent) return;

  let currentView = null;
  let cachedViews = {};

  function loadContent(url, isGroup = false) {
    if (cachedViews[url]) {
      mainContent.innerHTML = cachedViews[url];
      if (isGroup) attachFormHandler();
      setupForumInteractions();
      return;
    }

    fetch(url)
      .then((response) => response.text())
      .then((html) => {
        cachedViews[url] = html;
        mainContent.innerHTML = html;
        if (isGroup) attachFormHandler();
        setupForumInteractions();
      })
      .catch((error) => console.error("Error:", error));
  }

  function attachFormHandler() {
    const form = mainContent.querySelector("form");
    if (form) {
      form.setAttribute("action", "./processes/groups.php");
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        fetch(form.action, {
          method: "POST",
          body: formData,
        })
          .then((res) => res.text())
          .then(() => {
            const groupId = localStorage.getItem("currentGroupId");
            if (groupId) {
              Content(groupId, false);
            }
          })
          .catch((err) => console.error("Message send failed:", err));
      });
    }
  }

  function loadGroupContent(groupId, updateHistory = true) {
    fetch(`./public/groups.php?group_id=${groupId}`)
      .then((response) => response.text())
      .then((html) => {
        mainContent.innerHTML = html;
        attachFormHandler(); // Attach after DOM is updated

        if (updateHistory) {
          history.pushState({ group_id: groupId }, "", `?group_id=${groupId}`);
        } else {
          history.replaceState(
            { group_id: groupId },
            "",
            `?group_id=${groupId}`
          );
        }

        // localStorage.setItem("currentGroupId", groupId); // Persist group state
      })
      .catch((error) => {
        console.error("Error loading group:", error);
      });
  }

  function loadRegularContent(url, updateHistory = true) {
    currentView = { type: "regular", url: url };
    loadContent(url);

    if (updateHistory) {
      history.pushState({ view: "regular" }, "", url);
    }
  }

  document.querySelectorAll(".view-group").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const groupId = this.getAttribute("data-group-id");
      loadGroupContent(groupId);
    });
  });

  document.querySelectorAll(".chat-link, .question-link").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      loadRegularContent(this.getAttribute("href"));
    });
  });

  window.addEventListener("popstate", function (event) {
    if (event.state) {
      if (event.state.view === "group" && event.state.group_id) {
        loadGroupContent(event.state.group_id, false);
      } else {
        loadRegularContent(window.location.pathname, false);
      }
    }
  });

  const urlParams = new URLSearchParams(window.location.search);
  const groupIdFromUrl = urlParams.get("group_id");
  if (groupIdFromUrl) {
    loadGroupContent(groupIdFromUrl, false);
  }
}

function setupForumInteractions() {
  handleReplyForm();
  handleReplySubmission();
  handleActions();
  handleParentResponse();
  handleReplyToggle();
}
