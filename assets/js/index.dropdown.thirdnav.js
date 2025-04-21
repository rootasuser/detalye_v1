const dropdownBtns = document.querySelectorAll("[id^='dropdownToggle']");
const dropdownMenus = document.querySelectorAll("[id^='collapseDropdown']");

dropdownBtns.forEach((dropdownBtn, index) => {
  dropdownBtn.addEventListener("click", () => {
    const dropdownMenu = dropdownMenus[index];

    dropdownMenus.forEach((menu, i) => {
      if (i !== index) {
        menu.style.display = "none";
        menu.classList.remove("show-animation");
      }
    });

    if (dropdownMenu.style.display === "none" || dropdownMenu.style.display === "") {
      dropdownMenu.style.display = "flex";
      dropdownMenu.classList.add("show-animation");
    } else {
      dropdownMenu.style.display = "none";
      dropdownMenu.classList.remove("show-animation");
    }
  });
});

document.addEventListener("click", function (event) {
  dropdownMenus.forEach((dropdownMenu) => {
    if (!dropdownMenu.contains(event.target) && !dropdownBtns[dropdownMenus.indexOf(dropdownMenu)].contains(event.target)) {
      dropdownMenu.style.display = "none";
      dropdownMenu.classList.remove("show-animation");
    }
  });
});
