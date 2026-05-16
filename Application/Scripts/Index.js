// Open and Close Sidebar
const openAndCloseSidebarBtn = document.getElementById("open-and-close-sidebar");
const flexContainer = document.getElementsByClassName("flex-container")[0];
const main = document.getElementsByTagName("main")[0];

openAndCloseSidebarBtn.addEventListener("click", function () {
    if (flexContainer.classList.toggle("hide")) {
        main.style.marginLeft = "0";
        this.style.rotate = "180deg";
    } else {
        main.style.marginLeft = "270px";
        this.style.rotate = "0deg";
    }
});

document.addEventListener("DOMContentLoaded", function () {

    // Share panel toggle -- only on CollaborationPage
    const shareIcon = document.getElementById("share-icon");
    const shareContainer = document.getElementById("share-container");

    if (shareIcon && shareContainer) {
        let isVisible = false;
        shareIcon.addEventListener("click", function () {
            isVisible = !isVisible;
            shareContainer.style.display = isVisible ? "flex" : "none";
        });
    }

    // Can Edit toggle -- only on CollaborationPage
    const canEditCheckbox = document.getElementById("can_edit_checkbox");
    if (canEditCheckbox) {
        canEditCheckbox.addEventListener("change", function () {
            document.getElementById("can_edit").value = this.checked ? 1 : 0;
        });
    }

    // Add task form toggles -- show/hide inline add form per column
    document.querySelectorAll(".add-task-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const listId = this.dataset.list;
            const form = document.getElementById("add-form-" + listId);
            if (form) {
                form.classList.toggle("open");
            }
        });
    });

    document.querySelectorAll(".cancel-task-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const listId = this.dataset.list;
            const form = document.getElementById("add-form-" + listId);
            if (form) {
                form.classList.remove("open");
            }
        });
    });

    // New list form toggle
    const newListBtn = document.getElementById("new-list-btn");
    const newListForm = document.getElementById("new-list-form");
    const cancelNewList = document.getElementById("cancel-new-list");

    if (newListBtn && newListForm) {
        newListBtn.addEventListener("click", function () {
            newListForm.classList.toggle("open");
        });
    }

    if (cancelNewList && newListForm) {
        cancelNewList.addEventListener("click", function () {
            newListForm.classList.remove("open");
        });
    }

});
