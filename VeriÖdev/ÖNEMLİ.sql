DROP DATABASE IF EXISTS HalYonetimSistemi;
CREATE DATABASE HalYonetimSistemi CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE HalYonetimSistemi;

CREATE TABLE Mustahsiller (
    MustahsilID INT AUTO_INCREMENT PRIMARY KEY,
    AdSoyad VARCHAR(100) NOT NULL,
    Telefon VARCHAR(15) UNIQUE,
    Adres TEXT,
    Borc DECIMAL(10, 2) DEFAULT 0.00
);

CREATE TABLE Musteriler (
    MusteriID INT AUTO_INCREMENT PRIMARY KEY,
    AdSoyad VARCHAR(100) NOT NULL,
    Telefon VARCHAR(15) UNIQUE,
    Adres TEXT,
    Bakiye DECIMAL(10, 2) DEFAULT 0.00
);

CREATE TABLE Urunler (
    UrunID INT AUTO_INCREMENT PRIMARY KEY,
    UrunAdi VARCHAR(100) NOT NULL UNIQUE,
    Birim VARCHAR(20) NOT NULL
);

CREATE TABLE Stok (
    StokID INT AUTO_INCREMENT PRIMARY KEY,
    UrunID INT NOT NULL,
    MustahsilID INT NOT NULL,
    Miktar DECIMAL(10, 2) NOT NULL,
    AlisFiyati DECIMAL(10, 2) NOT NULL,
    GirisTarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    KalanMiktar DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (UrunID) REFERENCES Urunler(UrunID) ON DELETE RESTRICT,
    FOREIGN KEY (MustahsilID) REFERENCES Mustahsiller(MustahsilID) ON DELETE RESTRICT
);

CREATE TABLE OdemeYontemleri (
    OdemeYontemID INT AUTO_INCREMENT PRIMARY KEY,
    YontemAdi VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Satislar (
    SatisID INT AUTO_INCREMENT PRIMARY KEY,
    MusteriID INT NOT NULL,
    SatisTarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    ToplamTutar DECIMAL(10, 2) NOT NULL,
    OdemeYontemID INT,
    FOREIGN KEY (MusteriID) REFERENCES Musteriler(MusteriID) ON DELETE CASCADE,
    FOREIGN KEY (OdemeYontemID) REFERENCES OdemeYontemleri(OdemeYontemID) ON DELETE RESTRICT
);

CREATE TABLE SatisDetaylari (
    SatisDetayID INT AUTO_INCREMENT PRIMARY KEY,
    SatisID INT NOT NULL,
    UrunID INT NOT NULL,
    Miktar DECIMAL(10, 2) NOT NULL,
    BirimFiyat DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (SatisID) REFERENCES Satislar(SatisID) ON DELETE CASCADE,
    FOREIGN KEY (UrunID) REFERENCES Urunler(UrunID) ON DELETE RESTRICT
);

CREATE TABLE StokHareketLog (
    LogID INT AUTO_INCREMENT PRIMARY KEY,
    UrunID INT,
    EskiMiktar DECIMAL(10, 2),
    YeniMiktar DECIMAL(10, 2),
    IslemTipi VARCHAR(50),
    IslemTarihi DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO OdemeYontemleri (YontemAdi) VALUES
('Nakit'),
('Kredi Kartı'),
('Havale'),
('Çek');

DELIMITER $$

CREATE PROCEDURE sp_MusteriEkle(IN p_AdSoyad VARCHAR(100), IN p_Telefon VARCHAR(15), IN p_Adres TEXT)
BEGIN
    INSERT INTO Musteriler(AdSoyad, Telefon, Adres) VALUES (p_AdSoyad, p_Telefon, p_Adres);
END$$

CREATE PROCEDURE sp_MusteriGuncelle(IN p_MusteriID INT, IN p_AdSoyad VARCHAR(100), IN p_Telefon VARCHAR(15), IN p_Adres TEXT)
BEGIN
    UPDATE Musteriler SET AdSoyad = p_AdSoyad, Telefon = p_Telefon, Adres = p_Adres WHERE MusteriID = p_MusteriID;
END$$

CREATE PROCEDURE sp_MusteriSil(IN p_MusteriID INT)
BEGIN
    DELETE FROM Musteriler WHERE MusteriID = p_MusteriID;
END$$

CREATE PROCEDURE sp_MusteriListele()
BEGIN
    SELECT MusteriID, AdSoyad, Telefon, Adres, Bakiye FROM Musteriler ORDER BY AdSoyad;
END$$

CREATE PROCEDURE sp_UrunEkle(IN p_UrunAdi VARCHAR(100), IN p_Birim VARCHAR(20))
BEGIN
    INSERT INTO Urunler(UrunAdi, Birim) VALUES (p_UrunAdi, p_Birim);
END$$

CREATE PROCEDURE sp_StokGirisYap(IN p_UrunID INT, IN p_MustahsilID INT, IN p_Miktar DECIMAL(10,2), IN p_AlisFiyati DECIMAL(10,2))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
        INSERT INTO Stok(UrunID, MustahsilID, Miktar, AlisFiyati, KalanMiktar)
        VALUES (p_UrunID, p_MustahsilID, p_Miktar, p_AlisFiyati, p_Miktar);

        UPDATE Mustahsiller
        SET Borc = Borc + (p_Miktar * p_AlisFiyati)
        WHERE MustahsilID = p_MustahsilID;
    COMMIT;
END$$

CREATE PROCEDURE sp_OdemeYontemiEkle(IN p_YontemAdi VARCHAR(50))
BEGIN
    INSERT INTO OdemeYontemleri(YontemAdi) VALUES (p_YontemAdi);
END$$

CREATE PROCEDURE sp_SatisYap(
    IN p_MusteriID INT,
    IN p_UrunID INT,
    IN p_Miktar DECIMAL(10,2),
    IN p_SatisFiyati DECIMAL(10,2),
    IN p_OdemeYontemID INT
)
BEGIN
    DECLARE v_KalanStok DECIMAL(10,2);
    DECLARE v_ToplamTutar DECIMAL(10,2);
    DECLARE v_YeniSatisID INT;
    DECLARE v_DusulecekMiktar DECIMAL(10, 2) DEFAULT p_Miktar;
    DECLARE v_StokID INT;
    DECLARE v_StokKalanMiktar DECIMAL(10, 2);
    DECLARE c_stok_cursor CURSOR FOR SELECT StokID, KalanMiktar FROM Stok WHERE UrunID = p_UrunID AND KalanMiktar > 0 ORDER BY GirisTarihi;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET @done = TRUE;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    SELECT SUM(KalanMiktar) INTO v_KalanStok FROM Stok WHERE UrunID = p_UrunID;

    IF v_KalanStok IS NULL OR v_KalanStok < p_Miktar THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Yetersiz stok!';
    ELSE
        START TRANSACTION;
            SET v_ToplamTutar = p_Miktar * p_SatisFiyati;

            INSERT INTO Satislar(MusteriID, ToplamTutar, OdemeYontemID)
            VALUES (p_MusteriID, v_ToplamTutar, p_OdemeYontemID);
            SET v_YeniSatisID = LAST_INSERT_ID();

            INSERT INTO SatisDetaylari(SatisID, UrunID, Miktar, BirimFiyat)
            VALUES (v_YeniSatisID, p_UrunID, p_Miktar, p_SatisFiyati);

            OPEN c_stok_cursor;
            read_loop: LOOP
                FETCH c_stok_cursor INTO v_StokID, v_StokKalanMiktar;
                IF @done THEN
                    LEAVE read_loop;
                END IF;

                IF v_DusulecekMiktar <= v_StokKalanMiktar THEN
                    UPDATE Stok SET KalanMiktar = KalanMiktar - v_DusulecekMiktar WHERE StokID = v_StokID;
                    SET v_DusulecekMiktar = 0;
                ELSE
                    UPDATE Stok SET KalanMiktar = 0 WHERE StokID = v_StokID;
                    SET v_DusulecekMiktar = v_DusulecekMiktar - v_StokKalanMiktar;
                END IF;

                IF v_DusulecekMiktar = 0 THEN
                    LEAVE read_loop;
                END IF;
            END LOOP;
            CLOSE c_stok_cursor;
            SET @done = FALSE;

            UPDATE Musteriler SET Bakiye = Bakiye + v_ToplamTutar WHERE MusteriID = p_MusteriID;

        COMMIT;
    END IF;
END$$

CREATE PROCEDURE sp_MusteriOdemeYap(IN p_MusteriID INT, IN p_OdemeTutari DECIMAL(10,2))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    START TRANSACTION;
        UPDATE Musteriler SET Bakiye = Bakiye - p_OdemeTutari WHERE MusteriID = p_MusteriID;
    COMMIT;
END$$

CREATE FUNCTION fn_UrunToplamStok(p_UrunID INT)
RETURNS DECIMAL(10, 2)
DETERMINISTIC
BEGIN
    DECLARE toplamStok DECIMAL(10, 2);
    SELECT SUM(KalanMiktar) INTO toplamStok FROM Stok WHERE UrunID = p_UrunID;
    IF toplamStok IS NULL THEN
        SET toplamStok = 0.00;
    END IF;
    RETURN toplamStok;
END$$

CREATE TRIGGER trg_AfterStokUpdate_Log
AFTER UPDATE ON Stok
FOR EACH ROW
BEGIN
    IF OLD.KalanMiktar != NEW.KalanMiktar THEN
        INSERT INTO StokHareketLog(UrunID, EskiMiktar, YeniMiktar, IslemTipi)
        VALUES (OLD.UrunID, OLD.KalanMiktar, NEW.KalanMiktar, 'Guncelleme/Satis');
    END IF;
END$$

CREATE TRIGGER trg_AfterStokInsert_Log
AFTER INSERT ON Stok
FOR EACH ROW
BEGIN
    INSERT INTO StokHareketLog(UrunID, EskiMiktar, YeniMiktar, IslemTipi)
    VALUES (NEW.UrunID, 0, NEW.KalanMiktar, 'Yeni Stok Girisi');
END$$

DELIMITER ;  
