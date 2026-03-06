<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}
include 'db.php';

$job_query = $pdo->query("SELECT DISTINCT job_title FROM employee WHERE job_title != '' ORDER BY job_title");
$all_jobs = $job_query->fetchAll(PDO::FETCH_COLUMN);

$selected_jobs = isset($_GET['jobs']) ? (array)$_GET['jobs'] : [];

$sort = $_GET['sort'] ?? 'newest';
switch ($sort) {
  case 'oldest':
    $order_query = "hire_date ASC";
    break;
  case 'salary_desc':
    $order_query = "salary DESC";
    break;
  case 'salary_asc':
    $order_query = "salary ASC";
    break;
  default:
    $order_query = "hire_date DESC";
    break;
}

function getSortUrl($new_sort)
{
  $params = $_GET;
  $params['sort'] = $new_sort;
  $params['page'] = 1;
  return "?" . http_build_query($params);
}

$where_sql = "";
$params = [];
if (!empty($selected_jobs)) {
  $placeholders = str_repeat('?,', count($selected_jobs) - 1) . '?';
  $where_sql = " WHERE job_title IN ($placeholders) ";
  $params = $selected_jobs;
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM employee" . $where_sql);
$count_stmt->execute($params);
$total_employees = $count_stmt->fetchColumn();

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_employees / $limit);

$sql = "SELECT * FROM employee $where_sql ORDER BY $order_query LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="icons/pageicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Employees</title>
</head>

<body>
  <div class="flex items-center gap-4 p-8">
    <div class="max-w-[1200px] mx-auto w-full px-4">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Employees</h1>
        <a href="logout.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
          Logout
        </a>
      </div>

      <div class="flex flex-col gap-4 mb-6">
        <div class="flex flex-col items-start gap-4">
          <div class="flex justify-between w-full">
            <button onclick="openAddModal()"
              class="bg-green-500 text-white px-4 py-2 rounded shadow hover:bg-green-600 transition">
              + Add employee
            </button>
            <a href="export.php?<?= http_build_query($_GET) ?>"
              class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition flex items-center gap-2">
              Export to Excel
            </a>
          </div>
          <div class="flex justify-between w-full">
            <button onclick="deleteSelected()" id="deleteBtn"
              class="bg-red-500 text-white px-4 py-2 rounded shadow hover:bg-red-600 transition">
              - Delete Selected
            </button>
            <form action="import.php" method="POST" enctype="multipart/form-data" class="flex items-center border border-black pr-2 pl-2">
              <input type="file" name="excel_file" accept=".xlsx, .xls" class="text-sm">
              <button type="submit" class="bg-orange-500 text-white px-3 py-1 rounded text-sm">Upload Excel</button>
            </form>
          </div>
          <button type="button" onclick="toggleJobsMenu()"
            class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded shadow-sm hover:bg-gray-50 flex items-center gap-2">
            Jobs (<?= count($selected_jobs) ?>)
            <span class="text-xs">▼</span>
          </button>

          <div id="jobsMenu" class="hidden absolute left-0 mt-2 w-64 bg-white border rounded-lg shadow-xl z-50 p-4 border border-gray-200">
            <form method="GET" action="index.php">
              <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

              <div class="max-h-48 overflow-y-auto mb-3 scrollbar-thin">
                <?php foreach ($all_jobs as $job): ?>
                  <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-50 p-1 rounded transition">
                    <input type="checkbox" name="jobs[]" value="<?= htmlspecialchars($job) ?>"
                      <?= in_array($job, $selected_jobs) ? 'checked' : '' ?>
                      class="w-4 h-4 accent-blue-600">
                    <span class="text-sm text-gray-700"><?= htmlspecialchars($job) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>

              <div class="flex justify-between items-center border-t pt-2">
                <a href="?sort=<?= $sort ?>" class="text-xs text-red-500 hover:underline">Reset</a>
                <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700 transition">
                  Apply Filter
                </button>
              </div>
            </form>
          </div>
        </div>
        <div class="flex flex-wrap gap-2 bg-gray-50 p-3 rounded-lg border">
          <span class="text-sm font-medium text-gray-500 w-full mb-1">Sort by:</span>
          <a href="<?= getSortUrl('newest') ?>"
            class="px-3 py-1 text-sm rounded border transition <?= $sort == 'newest' ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
            Newest First
          </a>
          <a href="<?= getSortUrl('oldest') ?>"
            class="px-3 py-1 text-sm rounded border transition <?= $sort == 'oldest' ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
            Oldest First
          </a>
          <a href="<?= getSortUrl('salary_desc') ?>"
            class="px-3 py-1 text-sm rounded border transition <?= $sort == 'salary_desc' ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
            Salary: High to Low
          </a>
          <a href="<?= getSortUrl('salary_asc') ?>"
            class="px-3 py-1 text-sm rounded border transition <?= $sort == 'salary_asc' ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
            Salary: Low to High
          </a>
        </div>
      </div>

      <div class="overflow-x-auto bg-white rounded-lg shadow mb-6">
        <table class="w-full bg-gray-50 border border-gray-200">
          <thead class="bg-gray-100">
            <tr class="border-b text-center uppercase text-sm">
              <th class="p-4 sticky left-0 z-10"><input type="checkbox" id="selectAll" class="w-5 h-5 cursor-pointer rounded border-gray-300 text-red-600 focus:ring-red-500 accent-red-600"></th>
              <th class="p-4">First name</th>
              <th class="p-4">Last name</th>
              <th class="p-4">Email</th>
              <th class="p-4">Phone number</th>
              <th class="p-4">Salary</th>
              <th class="p-4">Job title</th>
              <th class="p-4">Hire date</th>
              <th class="p-4"></th>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($row = $stmt->fetch()): ?>
              <tr class="border-b even:bg-gray-100 hover:bg-blue-50 transition-colors" id="row-<?= $row['employee_id'] ?>">
                <td class="p-4 sticky left-0 z-10">
                  <input type="checkbox" class="employee-checkbox w-5 h-5 cursor-pointer rounded border-gray-300 text-red-600 focus:ring-red-500 accent-red-600" value="<?= $row['employee_id'] ?>">
                </td>
                <td class="p-4"><?= htmlspecialchars($row['first_name']) ?></td>
                <td class="p-4"><?= htmlspecialchars($row['last_name']) ?></td>
                <td class="p-4"><?= htmlspecialchars($row['email']) ?></td>
                <td class="p-4"><?= htmlspecialchars($row['phone_number']) ?></td>
                <td class="p-4 font-semibold">$<?= htmlspecialchars($row['salary']) ?></td>
                <td class="p-4 text-gray-600"><?= htmlspecialchars($row['job_title']) ?></td>
                <td class="p-4"><?= htmlspecialchars($row['hire_date']) ?></td>
                <td class="p-4 text-right">
                  <button onclick="editEmployee(<?= htmlspecialchars(json_encode($row)) ?>)"
                    class="bg-blue-600 hover:bg-blue-800 text-white py-1 px-4 rounded">
                    Edit
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <div class="mt-6 flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border">
          <div class="text-sm text-gray-600">
            Showing <strong><?= $offset + 1 ?></strong> to <strong><?= min($offset + $limit, $total_employees) ?></strong> of <strong><?= $total_employees ?></strong>
          </div>

          <div class="flex items-center gap-4">
            <div class="flex gap-1">
              <?php if ($page > 1): ?>
                <?php
                $current_params = $_GET;
                unset($current_params['page']);
                $url_params = http_build_query($current_params);
                $base_url = "?" . $url_params . "&page=";
                ?>
                <a href="?page=1&sort=<?= $sort ?>" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 transition" title="First Page">«</a>
                <a href="?page=<?= $page - 1 ?>&sort=<?= $sort ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 transition">Prev</a>
              <?php endif; ?>

              <span class="px-4 py-1 bg-blue-600 text-white rounded shadow-sm">
                Page <?= $page ?> of <?= $total_pages ?>
              </span>

              <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&sort=<?= $sort ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 transition">Next</a>
                <a href="?page=<?= $total_pages ?>&sort=<?= $sort ?>" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 transition" title="Last Page">»</a>
              <?php endif; ?>
            </div>

            <div class="flex items-center gap-2 border-l pl-4">
              <span class="text-sm text-gray-500">Go to:</span>
              <input type="number" id="jumpPage" min="1" max="<?= $total_pages ?>" placeholder="<?= $page ?>"
                class="w-16 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button onclick="goToPage()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">Go</button>
            </div>
          </div>
        </div>

        <script>
          function goToPage() {
            const pageValue = document.getElementById('jumpPage').value;
            const maxPage = <?= $total_pages ?>;

            if (pageValue >= 1 && pageValue <= maxPage) {
              const urlParams = new URLSearchParams(window.location.search);
              urlParams.set('page', pageValue);
              window.location.search = urlParams.toString();
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Invalid page',
                heightAuto: false
              });
            }
          }

          document.getElementById('jumpPage')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
              goToPage();
            }
          });
        </script>
      </div>

      <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-xl w-[500px]">
          <h2 class="text-xl font-bold mb-4">New Employee</h2>

          <form id="employeeForm" action="add_employee.php" method="POST" class="grid grid-cols-2 gap-4">
            <input type="hidden" name="employee_id" id="employee_id">
            <input type="text" name="first_name" id="first_name" placeholder="First name" class="border p-2 rounded w-full">
            <input type="text" name="last_name" id="last_name" placeholder="Last name" class="border p-2 rounded w-full">
            <input type="text" name="email" id="email" placeholder="Email" class="border p-2 rounded col-span-2">
            <input type="text" name="phone_number" id="phone_number" placeholder="Phone number" class="border p-2 rounded w-full">
            <input type="number" name="salary" id="salary" placeholder="Salary" class="border p-2 rounded w-full">
            <input type="text" name="job_title" id="job_title" placeholder="Job title" class="border p-2 rounded col-span-2">
            <input type="date" name="hire_date" id="hire_date" max="<?= date('Y-m-d'); ?>" class="border p-2 rounded col-span-2">

            <div class="col-span-2 flex justify-end gap-2 mt-4">
              <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Back</button>
              <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="employee.js"></script>
</body>

</html>