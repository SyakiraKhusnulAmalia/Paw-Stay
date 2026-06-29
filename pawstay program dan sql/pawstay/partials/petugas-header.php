<?php
// Partial: sidebar + navbar untuk area Petugas.
// Wajib set $activePage sebelum include file ini, contoh: $activePage = 'petugas-dashboard.php';
$namaPetugas = namaPetugas();
?>
<div class="admin-shell">
  <div class="sidebar-backdrop"></div>

  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <a class="brand-mark" href="petugas-dashboard.php">
        <span class="brand-icon">🐾</span>
        <span class="brand-copy"><span class="brand-title">PawStay</span><span class="brand-subtitle">Portal Petugas</span></span>
      </a>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Menu</div>
      <a class="nav-link <?= $activePage === 'petugas-dashboard.php' ? 'active' : '' ?>" href="petugas-dashboard.php">
        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span>
      </a>
      <a class="nav-link <?= $activePage === 'petugas-penitipan.php' ? 'active' : '' ?>" href="petugas-penitipan.php">
        <span class="nav-icon"><i class="bi bi-clipboard2-data"></i></span><span class="nav-text">Data Penitipan</span>
      </a>
      <a class="nav-link <?= $activePage === 'petugas-kondisi.php' ? 'active' : '' ?>" href="petugas-kondisi.php">
        <span class="nav-icon"><i class="bi bi-heart-pulse"></i></span><span class="nav-text">Kondisi Hewan</span>
      </a>
      <a class="nav-link <?= $activePage === 'petugas-penjemputan.php' ? 'active' : '' ?>" href="petugas-penjemputan.php">
        <span class="nav-icon"><i class="bi bi-box-arrow-in-right"></i></span><span class="nav-text">Penjemputan Hewan</span>
      </a>
      <a class="nav-link <?= $activePage === 'petugas-profile.php' ? 'active' : '' ?>" href="petugas-profile.php">
        <span class="nav-icon"><i class="bi bi-person"></i></span><span class="nav-text">Profil</span>
      </a>
    </nav>
    <div class="sidebar-user">
      <div class="avatar-placeholder avatar-md">👤</div>
      <div><strong><?= $namaPetugas ?></strong><small>Petugas</small></div>
    </div>
    <div class="sidebar-footer"><span class="status-dot"></span><span>Sistem berjalan normal</span></div>
  </aside>

  <div class="admin-main">
    <nav class="admin-navbar">
      <button class="sidebar-toggle" data-sidebar-toggle><span></span><span></span><span></span></button>
      <input class="search-input ms-3" type="search" placeholder="Cari…" style="flex:1;max-width:300px">
      <div class="navbar-actions">
        <button class="icon-button" data-theme-toggle><i class="bi bi-moon-stars" data-theme-icon></i></button>
        <div class="dropdown">
          <button class="profile-button dropdown-toggle">
            <div class="avatar-placeholder avatar-sm">👤</div>
            <span class="profile-name"><?= $namaPetugas ?></span>
          </button>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="petugas-profile.php"><i class="bi bi-person-gear"></i> Profil Saya</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="logout-petugas.php"><i class="bi bi-box-arrow-right"></i> Keluar</a>
          </div>
        </div>
      </div>
    </nav>

    <main class="dashboard-content">
