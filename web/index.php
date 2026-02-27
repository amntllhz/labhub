<?php
// web/index.php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// 1. Tangkap Parameter Filter & Pagination
$status_filter = $_GET['status'] ?? 'all';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // 2. Query Dasar & Filter
    $where_clause = "";
    $params = [];
    if ($status_filter !== 'all') {
        $where_clause = " WHERE status = :status";
        $params[':status'] = $status_filter;
    }

    // 3. Hitung Total Data (untuk pagination)
    $count_query = "SELECT COUNT(*) FROM datakendala" . $where_clause;
    $stmt_count = $db->prepare($count_query);
    $stmt_count->execute($params);
    $total_rows = $stmt_count->fetchColumn();
    $total_pages = ceil($total_rows / $limit);

    // 4. Ambil Data dengan Limit & Offset
    $query = "SELECT * FROM datakendala" . $where_clause . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    
    // Bind value secara manual untuk LIMIT & OFFSET karena PDO membutuhkan tipe integer
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabHub - Fastikom</title>
    <link href="public/dist/output.css" rel="stylesheet">
    <link rel="icon" href="../web/public/images/labhub-favicon.svg">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:ital,wght@0,200..800&display=swap" rel="stylesheet" />
</head>
<body class="bg-slate-50 font-sans antialiased">

    <nav class="bg-white border-b border-slate-200 px-12 py-4 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <img src="../web/public/images/labhub-logo-nav.svg" alt="" class="w-24">
            <div class="text-sm font-main text-slate-500">Total: <span class="font-bold text-slate-800"><?= count($reports) ?></span> Laporan</div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-12">
        <div class="flex w-full justify-between">
            <div class=" mb-6 px-4 border-l-4 border-primary">
                <h2 class="text-2xl font-main font-semibold text-slate-900">Daftar Laporan Kendala Labkom</h2>
                <p class="text-slate-500 font-main text-xs mt-1">Laporan terbaru yang dikirim melalui Labhub desktop Labkom</p>
            </div>
    
            <div class="flex items-center gap-3" x-data="{ openFilter: false }">
    
                <div class="relative">
                    <button @click="openFilter = !openFilter" @click.outside="openFilter = false" 
                            class="flex items-center cursor-pointer gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-main font-semibold text-slate-400 hover:bg-slate-50 transition">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-slate-400"></i>
                        Filter
                        <?php if($status_filter !== 'all'): ?>
                            <span class="w-1.5 h-1.5 bg-primary rounded-full"></span>
                        <?php endif; ?>
                    </button>

                    <div x-show="openFilter" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:leave="transition ease-in duration-75"
                        class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl z-30 py-2">
                        
                        <div class="px-4 py-2 text-xs text-slate-400 font-main tracking-wider">Status Laporan</div>
                        
                        <a href="?status=all&limit=<?= $limit ?>" 
                        class="flex items-center justify-between px-4 py-2 font-main text-xs <?= $status_filter == 'all' ? 'text-primary bg-blue-50/50' : 'text-slate-600 hover:bg-slate-50' ?>">
                            Semua Laporan
                            <?php if($status_filter == 'all'): ?> <i data-lucide="check" class="w-4 h-4"></i> <?php endif; ?>
                        </a>

                        <a href="?status=pending&limit=<?= $limit ?>" 
                        class="flex items-center justify-between px-4 py-2 font-main text-xs <?= $status_filter == 'pending' ? 'text-amber-600 bg-amber-50/50' : 'text-slate-600 hover:bg-slate-50' ?>">
                            Pending
                            <?php if($status_filter == 'pending'): ?> <i data-lucide="check" class="w-4 h-4"></i> <?php endif; ?>
                        </a>

                        <a href="?status=solved&limit=<?= $limit ?>" 
                        class="flex items-center justify-between px-4 py-2 font-main text-xs <?= $status_filter == 'solved' ? 'text-emerald-600 bg-emerald-50/50' : 'text-slate-600 hover:bg-slate-50' ?>">
                            Solved
                            <?php if($status_filter == 'solved'): ?> <i data-lucide="check" class="w-4 h-4"></i> <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 w-1/8 py-2 text-xs text-center font-semibold font-main tracking-wider text-gray-800">Tanggal</th>
                        <th class="px-6 w-1/8 py-2 text-xs text-center font-semibold font-main tracking-wider text-gray-800">NIM</th>                        
                        <th class="px-6 w-1/8 py-2 text-xs text-center font-semibold font-main tracking-wider text-gray-800">Kelas</th>
                        <th class="px-6 w-1/8 py-2 text-xs text-center font-semibold font-main tracking-wider text-gray-800">Lab</th>
                        <th class="px-6 w-1/8 py-2 text-xs text-center font-semibold font-main tracking-wider text-gray-800">Kendala</th>
                        <th class="px-6 w-1/8 py-2 text-xs text-center font-semibold font-main tracking-wider text-gray-800">Status</th>
                        <th class="px-6 w-1/8 py-2 text-xs text-center font-semibold font-main tracking-wider text-gray-800">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-xs text-center text-slate-500 font-main">Belum ada laporan kendala masuk</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 w-1/8 py-2.5 text-center font-main text-xs text-gray-500">
                                <?= htmlspecialchars(date('d-m-Y', strtotime($report['created_at']))) ?>
                            </td>                            
                            <td class="px-6 w-1/8 py-2.5 text-center font-main text-xs text-gray-500">
                                <?= htmlspecialchars($report['nim']) ?>
                            </td>                            
                            <td class="px-6 py-2.5 text-center text-xs">
                                <span class="px-2 py-1 bg-blue-50 text-primary rounded-sm text-[10px] font-medium font-main uppercase">
                                    <?= htmlspecialchars($report['kelas']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-2.5 text-center text-xs font-main text-gray-500">
                                <?= htmlspecialchars($report['lab']) ?>
                            </td>
                            <td class="px-6 py-2.5 text-center text-xs font-main text-gray-500 max-w-xs truncate" title="<?= $report['keluhan'] ?>">
                                <?= htmlspecialchars($report['keluhan']) ?>                            
                            </td>
                            <td class="text-center">
                                <?php if ($report['status'] == 'pending'): ?>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-400 rounded-sm text-[10px] font-medium font-main">Pending</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-blue-50 text-blue-400 rounded-sm text-[10px] font-medium font-main">Solved</span>
                                <?php endif; ?>
                            </td>  
                            <td class=" text-center">
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button @click="open = !open" @click.outside="open = false" class="p-2 hover:bg-slate-100 rounded-sm transition cursor-pointer">
                                        <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                    </button>

                                    <div x-show="open" 
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        class="absolute p-1 right-0 mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-lg z-20 py-1">
                                        
                                        <button onclick="updateStatus(<?= $report['id'] ?>, 'solved')" class="w-full font-main cursor-pointer text-left px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 flex items-center gap-2 rounded-sm">
                                            <i data-lucide="badge-check" class="w-3 h-3 text-emerald-500"></i> Tandai Solved
                                        </button>
                                        
                                        <button onclick="deleteReport(<?= $report['id'] ?>)" class="w-full font-main cursor-pointer text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50/80 flex items-center gap-2 rounded-sm">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i> Hapus Laporan
                                        </button>
                                    </div>
                                </div>
                            </td>                          
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table> 

            <div class="bg-white rounded-t-none rounded-b-lg border border-slate-200">
                <table class="w-full text-left border-collapse">
                    </table>

                <div class="bg-slate-50/50 border-slate-200 px-6 py-2 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs font-main text-slate-400">
                        <span>Tampilkan</span>
                        <select onchange="location.href='?status=<?= $status_filter ?>&limit=' + this.value" 
                                class="bg-white border border-slate-200 font-main rounded px-2 py-1 focus:outline focus:outline-blue-500">
                            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>                                                        
                        </select>
                        <span>data</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page-1 ?>&status=<?= $status_filter ?>&limit=<?= $limit ?>" 
                            class="p-2 hover:bg-white rounded-lg transition font-main text-slate-400">
                                <i data-lucide="chevron-left" class="w-5 h-5"></i>
                            </a>
                        <?php endif; ?>

                        <div class="px-4 py-1.5 bg-white rounded-lg text-xs font-medium font-main text-slate-400">
                            Halaman <?= $page ?> dari <?= $total_pages ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page+1 ?>&status=<?= $status_filter ?>&limit=<?= $limit ?>" 
                            class="p-2 hover:bg-white rounded-lg transition font-main text-slate-400">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>         
        </div>
    </main>

<script>
    // Inisialisasi ikon Lucide
    lucide.createIcons();

    function updateStatus(id, status) {
        if(confirm('Ubah status laporan menjadi Solved?')) {
            window.location.href = `api/update_report.php?action=status&id=${id}&status=${status}`;
        }
    }

    function deleteReport(id) {
        if(confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')) {
            window.location.href = `api/update_report.php?action=delete&id=${id}`;
        }
    }
</script>
</body>
</html>