CREATE TABLE pagamentos (
    pid INT AUTO_INCREMENT PRIMARY KEY,
    cid INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_pagamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) NOT NULL,
    FOREIGN KEY (cid) REFERENCES catequizando(cid),
    FOREIGN KEY (username) REFERENCES utilizador(username)
);
