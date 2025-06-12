<?php
$db = new Database();
$conn = $db->getConnection();


$stmt = $conn->query("
    SELECT u.*, fn_UrunToplamStok(u.UrunID) as ToplamStok
    FROM Urunler u
    ORDER BY u.UrunAdi
");
$stokRaporu = $stmt->fetchAll();


$stmt = $conn->query("
    SELECT l.*, u.UrunAdi
    FROM StokHareketLog l
    JOIN Urunler u ON l.UrunID = u.UrunID
    ORDER BY l.IslemTarihi DESC
    LIMIT 100
");
$hareketLoglari = $stmt->fetchAll();
?>

<h2 class="mb-4">Raporlar</h2>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ürün Stok Raporu</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ürün Adı</th>
                                <th>Birim</th>
                                <th>Toplam Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stokRaporu as $urun): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($urun['UrunAdi']); ?></td>
                                    <td><?php echo htmlspecialchars($urun['Birim']); ?></td>
                                    <td><?php echo number_format($urun['ToplamStok'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Stok Hareket Logları</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Ürün</th>
                                <th>İşlem Tipi</th>
                                <th>Eski Miktar</th>
                                <th>Yeni Miktar</th>
                                <th>Değişim</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hareketLoglari as $log): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($log['IslemTarihi'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['UrunAdi']); ?></td>
                                    <td><?php echo htmlspecialchars($log['IslemTipi']); ?></td>
                                    <td><?php echo number_format($log['EskiMiktar'], 2); ?></td>
                                    <td><?php echo number_format($log['YeniMiktar'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $degisim = $log['YeniMiktar'] - $log['EskiMiktar'];
                                        $class = $degisim > 0 ? 'text-success' : ($degisim < 0 ? 'text-danger' : '');
                                        echo '<span class="' . $class . '">' . 
                                             ($degisim > 0 ? '+' : '') . 
                                             number_format($degisim, 2) . 
                                             '</span>';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 