document
  .getElementById("employeeForm")
  .addEventListener("submit", function (e) {
    const firstName = document.getElementById("first_name").value.trim();
    const lastName = document.getElementById("last_name").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone_number").value.trim();
    const salary = document.getElementById("salary").value.trim();
    const job = document.getElementById("job_title").value.trim();
    const hireDate = document.getElementById("hire_date").value.trim();

    if (!firstName || !lastName || !email || !salary || !job || !hireDate) {
      e.preventDefault();
      showError("All fields must be filled!");
      return;
    }

    const nameRegex = /^[^\d]+$/;
    if (!nameRegex.test(firstName) || !nameRegex.test(lastName)) {
      e.preventDefault();
      showError("In first name and last name should not be digits!");
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      e.preventDefault();
      showError("Print correct email!");
      return;
    }

    const phoneRegex = /^\+?\d{10,15}$/;
    if (phone !== "") {
      if (!phoneRegex.test(phone.replace(/[\s\-]/g, ""))) {
        e.preventDefault();
        showError("Phone number must be between 10 and 15 digits!");
        return;
      }
    }
  });

function showError(message) {
  Swal.fire({
    title: "Validation error!",
    text: message,
    icon: "error",
    confirmButtonColor: "#3b82f6",
    heightAuto: false,
  });
}

function getSelectedIds() {
  const ids = localStorage.getItem("selectedEmployees");
  return ids ? JSON.parse(ids) : [];
}

function saveSelectedIds(ids) {
  localStorage.setItem("selectedEmployees", JSON.stringify(ids));
}

function updateDeleteCounter() {
  const selectedIds = getSelectedIds();
  const btn = document.getElementById("deleteBtn");
  const count = selectedIds.length;
  btn.innerText =
    count > 0 ? `- Delete Selected (${count})` : `- Delete Selected`;
}

document.addEventListener("DOMContentLoaded", () => {
  const selectedIds = getSelectedIds();
  document.querySelectorAll(".employee-checkbox").forEach((cb) => {
    if (selectedIds.includes(cb.value)) cb.checked = true;
  });
  updateDeleteCounter();
});

function editEmployee(data) {
  document.querySelector("#addModal h2").innerText = "Edit Employee";
  document.getElementById("employeeForm").action = "update_employee.php";

  document.getElementById("employee_id").value = data.employee_id;
  document.getElementById("first_name").value = data.first_name;
  document.getElementById("last_name").value = data.last_name;
  document.getElementById("email").value = data.email;
  document.getElementById("phone_number").value = data.phone_number;
  document.getElementById("salary").value = data.salary;
  document.getElementById("job_title").value = data.job_title;
  document.getElementById("hire_date").value = data.hire_date;

  document.getElementById("addModal").classList.remove("hidden");
}

function openAddModal() {
  document.querySelector("#addModal h2").innerText = "New Employee";
  document.getElementById("employeeForm").action = "add_employee.php";
  document.getElementById("employeeForm").reset();
  document.getElementById("employee_id").value = "";
  document.getElementById("addModal").classList.remove("hidden");
}

document.getElementById("selectAll").addEventListener("change", function () {
  const checkboxes = document.querySelectorAll(".employee-checkbox");
  checkboxes.forEach((cb) => (cb.checked = this.checked));
  toggleDeleteButton();
});

function deleteSelected() {
  const ids = getSelectedIds();

  if (ids.length === 0) {
    Swal.fire({
      title: "No selection!",
      text: "Please select at least one employee to delete.",
      icon: "info",
      confirmButtonColor: "#3b82f6",
      heightAuto: false,
    });
    return;
  }

  Swal.fire({
    title: "Are you sure?",
    text: `You are about to delete ${ids.length} employee(s) across all pages!`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, delete them!",
    heightAuto: false,
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append("ids", JSON.stringify(ids));

      fetch("delete_employees.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            ids.forEach((id) => {
              const row = document.getElementById("row-" + id);
              if (row) row.remove();
            });

            localStorage.removeItem("selectedEmployees");
            document.getElementById("selectAll").checked = false;
            updateDeleteCounter();

            Swal.fire({
              title: "Deleted!",
              text: "Selected employees have been removed.",
              icon: "success",
              timer: 1500,
              showConfirmButton: false,
              heightAuto: false,
            }).then(() => {
              location.reload();
            });
          }
        });
    }
  });
}

document.addEventListener("change", function (e) {
  let selectedIds = getSelectedIds();

  if (e.target.classList.contains("employee-checkbox")) {
    const id = e.target.value;
    if (e.target.checked) {
      if (!selectedIds.includes(id)) selectedIds.push(id);
    } else {
      selectedIds = selectedIds.filter((item) => item !== id);
    }
    saveSelectedIds(selectedIds);
    updateDeleteCounter();
  }

  if (e.target.id === "selectAll") {
    const checkboxes = document.querySelectorAll(".employee-checkbox");
    checkboxes.forEach((cb) => {
      cb.checked = e.target.checked;
      if (e.target.checked) {
        if (!selectedIds.includes(cb.value)) selectedIds.push(cb.value);
      } else {
        selectedIds = selectedIds.filter((item) => item !== cb.value);
      }
    });
    saveSelectedIds(selectedIds);
    updateDeleteCounter();
  }
});

function toggleJobsMenu() {
  const menu = document.getElementById("jobsMenu");
  menu.classList.toggle("hidden");
}
window.addEventListener("click", function (e) {
  const container = document.getElementById("jobsContainer");
  const menu = document.getElementById("jobsMenu");
  if (container && !container.contains(e.target)) {
    menu.classList.add("hidden");
  }
});
