<?php
$db = new Database();
$conn = $db->getConnection();


$stmt = $conn->query("SELECT COUNT(*) as total FROM Urunler");
$totalProducts = $stmt->fetch()['total'];


$stmt = $conn->query("SELECT SUM(KalanMiktar * AlisFiyati) as toplam FROM Stok");
$totalStock = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'] ?? 0;


$stmt = $conn->query("SELECT SUM(Borc) as total FROM Mustahsiller");
$totalDebt = $stmt->fetch()['total'] ?? 0;


$stmt = $conn->query("SELECT SUM(Bakiye) as total FROM Musteriler");
$totalBalance = $stmt->fetch()['total'] ?? 0;
?>

<h2 class="mb-4">Kontrol Paneli</h2>

<div class="row">
    <div class="col-md-3">
        <div class="card summary-card total-products">
            <div class="card-body">
                <h5 class="card-title">Toplam Ürün</h5>
                <h3 class="card-text"><?php echo number_format($totalProducts); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card total-stock">
            <div class="card-body">
                <h5 class="card-title">Toplam Stok Değeri</h5>
                <h3 class="card-text"><?php echo number_format($totalStock, 2); ?> ₺</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card total-debt">
            <div class="card-body">
                <h5 class="card-title">Toplam Borç</h5>
                <h3 class="card-text"><?php echo number_format($totalDebt, 2); ?> ₺</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card total-balance">
            <div class="card-body">
                <h5 class="card-title">Toplam Bakiye</h5>
                <h3 class="card-text"><?php echo number_format($totalBalance, 2); ?> ₺</h3>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Son Satışlar</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Müşteri</th>
                                <th>Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("
                                SELECT s.SatisTarihi, m.AdSoyad, s.ToplamTutar 
                                FROM Satislar s 
                                JOIN Musteriler m ON s.MusteriID = m.MusteriID 
                                ORDER BY s.SatisTarihi DESC 
                                LIMIT 5
                            ");
                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>" . date('d.m.Y H:i', strtotime($row['SatisTarihi'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['AdSoyad']) . "</td>";
                                echo "<td>" . number_format($row['ToplamTutar'], 2) . " ₺</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Stok Durumu</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Kalan Miktar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("
                                SELECT u.UrunAdi, SUM(s.KalanMiktar) as ToplamMiktar 
                                FROM Urunler u 
                                LEFT JOIN Stok s ON u.UrunID = s.UrunID 
                                GROUP BY u.UrunID 
                                ORDER BY ToplamMiktar DESC 
                                LIMIT 5
                            ");
                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['UrunAdi']) . "</td>";
                                echo "<td>" . number_format($row['ToplamMiktar'] ?? 0, 2) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 