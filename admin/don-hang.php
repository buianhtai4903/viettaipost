<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đơn hàng</title>
    <link rel="stylesheet" href="../asset/css/bootstrap.min.css">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<?php 

require_once '../controller/cls-admin.php';
$admin = new clsAdmin();
$tat_ca_don_hang = $admin->layTatCaDonHang();
?>
<body  class="d-flex">
    <?php
    require_once 'view/sidebar.php';
    ?>
    
    <div class="main-content">

    <?php
    require_once 'view/header.php';
    ?>
<div class="container px-4 pb-5 card border rounded-4 mt-5">
        <h3 class="mt-4">Danh sách đơn hàng</h3>
        <table class="table table-bordered table-hover mt-3">
            <thead class="table-light">
                <tr>
                    <th>Mã ĐH</th>
                    <th>Tên ĐH</th>
                    <th>Người gửi</th>
                    <th>Người nhận</th>
                    <th>Trạng thái</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tat_ca_don_hang as $don): ?>
                <tr>
                    <td><?= $don['ma_don_hang'] ?></td>
                    <td><?= $don['ten_don_hang'] ?></td>
                    <td><?= $don['ten_nguoi_gui'] ?><br><?= $don['dia_chi_nguoi_gui'] ?><br><?= $don['dia_chi_nguoi_gui_mac_dinh'] ?></td>
                    <td><?= $don['ten_nguoi_nhan'] ?><br><?= $don['dia_chi_nguoi_nhan'] ?><br><?= $don['dia_chi_nguoi_nhan_mac_dinh'] ?></td>
                    <td><?= $don['trang_thai'] ?></td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?= $don['ma_don_hang'] ?>">
                            Chi tiết
                        </button>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="modal<?= $don['ma_don_hang'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $don['ma_don_hang'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel<?= $don['ma_don_hang'] ?>">Chi tiết đơn hàng <?= $don['ma_don_hang'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">

                                    <div class = "col-md-7">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><strong>Tên đơn hàng:</strong> <?= $don['ten_don_hang'] ?></li>
                                        <li class="list-group-item"><strong>Số lượng:</strong> <?= $don['so_luong'] ?></li>
                                        <li class="list-group-item"><strong>Trọng lượng:</strong> <?= $don['trong_luong'] ?> gram</li>
                                        <li class="list-group-item"><strong>Người gửi:</strong> <?= $don['ten_nguoi_gui'] ?> (<?= $don['sdt_nguoi_gui'] ?>)</li>
                                        <li class="list-group-item"><strong>Địa chỉ gửi:</strong> <?= $don['dia_chi_nguoi_gui'] ?> - <?= $don['dia_chi_nguoi_gui_mac_dinh'] ?></li>
                                        <li class="list-group-item"><strong>Người nhận:</strong> <?= $don['ten_nguoi_nhan'] ?> (<?= $don['sdt_nguoi_nhan'] ?>)</li>
                                        <li class="list-group-item"><strong>Địa chỉ nhận:</strong> <?= $don['dia_chi_nguoi_nhan'] ?> - <?= $don['dia_chi_nguoi_nhan_mac_dinh'] ?></li>
                                        <li class="list-group-item"><strong>Thu hộ:</strong> <?= number_format($don['thu_ho']) ?>đ</li>
                                        <li class="list-group-item"><strong>Phí vận chuyển:</strong> <?= number_format($don['phi_van_chuyen']) ?>đ</li>
                                        <li class="list-group-item"><strong>Người trả phí:</strong> <?= $don['nguoi_tra_phi'] ?></li>
                                        <li class="list-group-item"><strong>Trạng thái:</strong> <?= $don['trang_thai'] ?></li>
                                        <li class="list-group-item"><strong>Ngày tạo:</strong> <?= $don['ngay_tao'] ?></li>
                                        <li class="list-group-item"><strong>Thời gian hẹn lấy:</strong> <?= $don['thoi_gian_hen_lay'] ?></li>
                                        <li class="list-group-item"><strong>Ngày giao dự kiến:</strong> <?= $don['ngay_giao_du_kien'] ?></li>
                                        <li class="list-group-item"><strong>Ghi chú:</strong> <?= $don['ghi_chu'] ?></li>
                                    </ul>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-primary text-white">Theo dõi kiện hàng</div>
                                            <div class="card-body">
                                                <?php
                                                $van_dons = $admin->layVanDonTheoMaDon($don['ma_don_hang']);
                                                $hasTracking = false;

                                                foreach ($van_dons as $vd) {
                                                    switch ($vd['trang_thai']) {
                                                        case 'đợi lấy hàng':
                                                            $hasTracking = true;
                                                            echo "<p><strong>🕐 Đợi lấy hàng:</strong><br>📌 {$vd['lich_su']}<br>👤 Shipper: {$vd['ten_shipper']} ({$vd['sdt_shipper']})</p><hr>";
                                                            break;
                                                        case 'đã lấy hàng':
                                                            $hasTracking = true;
                                                            echo "<p><strong>✅ Đã lấy hàng:</strong><br>📌 {$vd['lich_su']}</p><hr>";
                                                            break;
                                                        case 'ở bưu cục':
                                                            $hasTracking = true;
                                                            echo "<p><strong>🏢 Đang ở bưu cục:</strong><br>📌 {$vd['lich_su']}<br></p><hr>";
                                                            break;
                                                         case 'trong xe':
                                                            $hasTracking = true;
                                                            echo "<p><strong>🚚 Đang giao:</strong><br>📌 {$vd['lich_su']}<br></p><hr>";
                                                            break;
                                                        case 'đang đi giao':
                                                            $hasTracking = true;
                                                            echo "<p><strong>🚚 Đang giao:</strong><br>📌 {$vd['lich_su']}<br>👤 Shipper: {$vd['ten_shipper']} ({$vd['sdt_shipper']})</p><hr>";
                                                            break;
                                                        case 'hủy':
                                                            $hasTracking = true;
                                                            echo "<p><strong>❌ Đã bị hủy:</strong><br>📌 {$vd['lich_su']}</p>";
                                                            break;
                                                        case 'giao thành công':
                                                            $hasTracking = true;
                                                            echo "<p><strong>🎉 Đã giao thành công:</strong><br>📌 {$vd['lich_su']}<br>👤 Shipper: {$vd['ten_shipper']} ({$vd['sdt_shipper']})</p>";
                                                            break;
                                                    }
                                                }

                                                if (!$hasTracking) {
                                                    echo "<p class='text-muted'>Chưa có thông tin theo dõi.</p>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>     
                            </div>

                            

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>



</body>
</html>


