<?php
// Included by any page that touches payments. Creates the tables the
// payment flow needs the first time it runs, so there's no separate
// manual SQL step to remember on the live host.
global $pdo;

$pdo->exec("
    CREATE TABLE IF NOT EXISTS tblwallettopups (
        topupID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        pfPaymentId VARCHAR(100) NULL,
        status ENUM('pending','complete','failed') NOT NULL DEFAULT 'pending',
        dateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userID) REFERENCES tblusers(userID)
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS tblpurchases (
        purchaseID INT AUTO_INCREMENT PRIMARY KEY,
        buyerID INT NOT NULL,
        sellerID INT NOT NULL,
        listingID INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        receiptNr VARCHAR(50) NOT NULL,
        dateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (buyerID) REFERENCES tblusers(userID),
        FOREIGN KEY (sellerID) REFERENCES tblusers(userID),
        FOREIGN KEY (listingID) REFERENCES tbllistings(listingID)
    )
");