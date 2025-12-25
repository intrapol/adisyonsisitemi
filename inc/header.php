<?php 
session_start();
include_once 'db/db.php';
include_once 'security.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adisyon Sistemi</title>
    
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS - Dark Mode -->
    <style>
        /* Karanlık Mod Temel Stilleri */
        :root {
            --dark-bg-primary: #0f1419;
            --dark-bg-secondary: #1a1f2e;
            --dark-bg-tertiary: #252b3a;
            --dark-text-primary: #e4e6eb;
            --dark-text-secondary: #b0b3b8;
            --dark-border: #2d3441;
            --dark-card-bg: #1e2330;
            --dark-header-gradient: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            --dark-accent: #4a9eff;
            --dark-success: #48bb78;
            --dark-danger: #f56565;
            --dark-warning: #ed8936;
            --dark-info: #4299e1;
        }

        body {
            background: var(--dark-bg-primary) !important;
            color: var(--dark-text-primary) !important;
            min-height: 100vh;
        }

        .header {
            background: var(--dark-header-gradient) !important;
            color: white !important;
            padding: 2rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .header .nav-link {
            color: white !important;
            transition: all 0.3s ease;
        }

        .header .nav-link:hover {
            color: #ffd700 !important;
            transform: translateY(-2px);
        }

        /* Card Stilleri */
        .card {
            background-color: var(--dark-card-bg) !important;
            border: 1px solid var(--dark-border) !important;
            color: var(--dark-text-primary) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3) !important;
        }

        .card-header {
            background-color: var(--dark-bg-tertiary) !important;
            border-bottom: 1px solid var(--dark-border) !important;
            color: var(--dark-text-primary) !important;
        }

        .card-body {
            color: var(--dark-text-primary) !important;
        }

        /* Table Stilleri */
        .table {
            color: var(--dark-text-primary) !important;
        }

        .table-bordered {
            border-color: var(--dark-border) !important;
        }

        .table-bordered td,
        .table-bordered th {
            border-color: var(--dark-border) !important;
        }

        .table thead th {
            background-color: var(--dark-bg-tertiary) !important;
            color: var(--dark-text-primary) !important;
            border-color: var(--dark-border) !important;
        }

        .table tbody tr {
            background-color: var(--dark-card-bg) !important;
        }

        .table tbody tr:hover {
            background-color: var(--dark-bg-tertiary) !important;
        }

        .table-success {
            background-color: rgba(72, 187, 120, 0.2) !important;
            color: var(--dark-success) !important;
        }

        .table-warning {
            background-color: rgba(237, 137, 54, 0.2) !important;
            color: var(--dark-warning) !important;
        }

        /* Button Stilleri */
        .btn-primary {
            background-color: var(--dark-accent) !important;
            border-color: var(--dark-accent) !important;
            color: white !important;
        }

        .btn-primary:hover {
            background-color: #3a7fd4 !important;
            border-color: #3a7fd4 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(74, 158, 255, 0.3);
        }

        .btn-success {
            background-color: var(--dark-success) !important;
            border-color: var(--dark-success) !important;
            color: white !important;
        }

        .btn-success:hover {
            background-color: #38a169 !important;
            border-color: #38a169 !important;
        }

        .btn-danger {
            background-color: var(--dark-danger) !important;
            border-color: var(--dark-danger) !important;
            color: white !important;
        }

        .btn-danger:hover {
            background-color: #e53e3e !important;
            border-color: #e53e3e !important;
        }

        .btn-warning {
            background-color: var(--dark-warning) !important;
            border-color: var(--dark-warning) !important;
            color: white !important;
        }

        .btn-warning:hover {
            background-color: #dd6b20 !important;
            border-color: #dd6b20 !important;
        }

        .btn-info {
            background-color: var(--dark-info) !important;
            border-color: var(--dark-info) !important;
            color: white !important;
        }

        .btn-info:hover {
            background-color: #3182ce !important;
            border-color: #3182ce !important;
        }

        /* Background color classes */
        .bg-info {
            background-color: rgba(66, 153, 225, 0.2) !important;
            border-color: var(--dark-info) !important;
        }

        .bg-primary {
            background-color: var(--dark-accent) !important;
        }

        .bg-success {
            background-color: var(--dark-success) !important;
        }

        .bg-danger {
            background-color: var(--dark-danger) !important;
        }

        .bg-warning {
            background-color: rgba(237, 137, 54, 0.2) !important;
        }

        .bg-dark {
            background-color: var(--dark-bg-tertiary) !important;
        }

        .btn-secondary {
            background-color: #4a5568 !important;
            border-color: #4a5568 !important;
            color: white !important;
        }

        .btn-secondary:hover {
            background-color: #2d3748 !important;
            border-color: #2d3748 !important;
        }

        .btn-outline-primary {
            border-color: var(--dark-accent) !important;
            color: var(--dark-accent) !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--dark-accent) !important;
            border-color: var(--dark-accent) !important;
            color: white !important;
        }

        .btn-outline-success {
            border-color: var(--dark-success) !important;
            color: var(--dark-success) !important;
        }

        .btn-outline-success:hover {
            background-color: var(--dark-success) !important;
            border-color: var(--dark-success) !important;
            color: white !important;
        }

        .btn-outline-warning {
            border-color: var(--dark-warning) !important;
            color: var(--dark-warning) !important;
        }

        .btn-outline-warning:hover {
            background-color: var(--dark-warning) !important;
            border-color: var(--dark-warning) !important;
            color: white !important;
        }

        .btn-outline-danger {
            border-color: var(--dark-danger) !important;
            color: var(--dark-danger) !important;
        }

        .btn-outline-danger:hover {
            background-color: var(--dark-danger) !important;
            border-color: var(--dark-danger) !important;
            color: white !important;
        }

        /* Alert Stilleri */
        .alert {
            background-color: var(--dark-card-bg) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text-primary) !important;
        }

        .alert-success {
            background-color: rgba(72, 187, 120, 0.2) !important;
            border-color: var(--dark-success) !important;
            color: var(--dark-success) !important;
        }

        .alert-danger {
            background-color: rgba(245, 101, 101, 0.2) !important;
            border-color: var(--dark-danger) !important;
            color: var(--dark-danger) !important;
        }

        .alert-warning {
            background-color: rgba(237, 137, 54, 0.2) !important;
            border-color: var(--dark-warning) !important;
            color: var(--dark-warning) !important;
        }

        .alert-info {
            background-color: rgba(66, 153, 225, 0.2) !important;
            border-color: var(--dark-info) !important;
            color: var(--dark-info) !important;
        }

        /* Form Stilleri */
        .form-control,
        .form-select {
            background-color: var(--dark-bg-tertiary) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text-primary) !important;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--dark-bg-tertiary) !important;
            border-color: var(--dark-accent) !important;
            color: var(--dark-text-primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(74, 158, 255, 0.25);
        }

        .form-control::placeholder {
            color: var(--dark-text-secondary) !important;
        }

        .form-label {
            color: var(--dark-text-primary) !important;
        }

        /* Badge Stilleri */
        .badge {
            color: white !important;
        }

        .badge.bg-dark {
            background-color: var(--dark-bg-tertiary) !important;
        }

        .badge.bg-success {
            background-color: var(--dark-success) !important;
        }

        .badge.bg-warning {
            background-color: var(--dark-warning) !important;
            color: white !important;
        }

        .badge.bg-danger {
            background-color: var(--dark-danger) !important;
        }

        /* Modal Stilleri */
        .modal-content {
            background-color: var(--dark-card-bg) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text-primary) !important;
        }

        .modal-header {
            background-color: var(--dark-bg-tertiary) !important;
            border-bottom-color: var(--dark-border) !important;
        }

        .modal-footer {
            background-color: var(--dark-bg-tertiary) !important;
            border-top-color: var(--dark-border) !important;
        }

        .btn-close {
            filter: invert(1);
        }

        /* Text Colors */
        .text-muted {
            color: var(--dark-text-secondary) !important;
        }

        .text-white {
            color: white !important;
        }

        /* Scrollbar Stilleri */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark-bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--dark-bg-tertiary);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--dark-border);
        }

        /* Link Stilleri */
        a {
            color: var(--dark-accent) !important;
        }

        a:hover {
            color: #3a7fd4 !important;
        }

        /* Hr Stilleri */
        hr {
            border-color: var(--dark-border) !important;
        }

        /* Input readonly stilleri */
        input[readonly] {
            background-color: var(--dark-bg-tertiary) !important;
            color: var(--dark-text-primary) !important;
        }
    </style>
</head>
<body>

    <!-- Küçük Header ve Menü -->
    <!-- Menü ve üst bar -->
    <div class="header py-2" style="background: linear-gradient(90deg, #388e3c 0%, #43a047 100%); box-shadow: 0 2px 8px rgba(34,34,34,0.05);">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo ve Başlık -->
                <div class="col-auto d-flex align-items-center">
                    
                    <span class="h4 align-middle ms-2 fw-bold" style="color: #fff; letter-spacing:1px;">Yeşilova Alabalık Pos</span>
                </div>
                <!-- Menü -->
                <!-- Bootstrap Icons CSS dosyasının yüklü olduğundan emin olun -->
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
                <div class="col d-flex justify-content-center">
                    <nav>
                        <ul class="nav">
                            <li class="nav-item mx-1">
                                <a class="nav-link text-white d-flex align-items-center px-3 py-1 rounded" href="/index.php" style="transition: background 0.2s;">
                                    <span class="d-inline-block align-middle" style="width: 1.5rem; text-align: center;">
                                        <i class="bi bi-house-fill" style="font-size:1.2rem; vertical-align: middle; color: #fff;"></i>
                                    </span>
                                    <strong class="ms-2">Anasayfa</strong>
                                </a>
                            </li>
                            <li class="nav-item mx-1">
                                <a class="nav-link text-white d-flex align-items-center px-3 py-1 rounded" href="masalar.php" style="transition: background 0.2s;">
                                    <span class="d-inline-block align-middle" style="width: 1.5rem; text-align: center; color: #fff;">
                                        <i class="bi bi-table" style="font-size:1.2rem; vertical-align: middle;"></i>
                                    </span>
                                    <strong class="ms-2">Masalar</strong>
                                </a>
                            </li>
                            <li class="nav-item mx-1">
                                <a class="nav-link text-white d-flex align-items-center px-3 py-1 rounded" href="../mutfak.php" style="transition: background 0.2s;">
                                    <span class="d-inline-block align-middle" style="width: 1.5rem; text-align: center;">
                                        <i class="bi bi-egg-fried" style="font-size:1.2rem; vertical-align: middle; color: #fff;"></i>
                                    </span>
                                    <strong class="ms-2">Mutfak</strong>
                                </a>                                
                            </li>
                            <li class="nav-item mx-1">
                                <a class="nav-link text-white d-flex align-items-center px-3 py-1 rounded" href="/yonetim/" style="transition: background 0.2s;">
                                    <span class="d-inline-block align-middle" style="width: 1.5rem; text-align: center;">
                                        <i class="bi bi-gear-fill" style="font-size:1.2rem; vertical-align: middle; color: #fff;"></i>
                                    </span>
                                    <strong class="ms-2">Yönetim</strong>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <!-- Yönetim (En sağda) -->
                <!-- Not: Bootstrap Icons kullanılıyor. <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css"> dosyanızda yüklü olmalı. -->
                
            </div>
        </div>
    </div>