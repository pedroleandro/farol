-- ===================================================================
-- FAROL — Estrutura do banco de dados
-- Papéis via enum simples na tabela users (sem tabelas de RBAC).
-- Campos de verificação de e-mail e reset de senha mantidos para uso
-- futuro, mas não utilizados na primeira versão.
-- ===================================================================

CREATE TABLE users
(
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                        VARCHAR(150)                       NOT NULL,
    email                       VARCHAR(150)                       NOT NULL,
    password                    VARCHAR(255)                       NOT NULL,
    role                        ENUM('admin', 'manager', 'operator', 'viewer') NOT NULL DEFAULT 'viewer',
    avatar                      VARCHAR(255)                       NULL,
    is_active                   TINYINT(1)  DEFAULT 1               NOT NULL,

    -- reservado para uso futuro (não implementado na v1)
    email_verified_at           DATETIME                           NULL,
    email_verification_token    VARCHAR(64)                        NULL,
    email_verification_sent_at  DATETIME                           NULL,
    reset_token                 VARCHAR(64)                        NULL,
    reset_expires_at            DATETIME                           NULL,

    created_at                  DATETIME    DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at                  DATETIME    DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at                  DATETIME                           NULL,

    CONSTRAINT uq_users_email UNIQUE (email)
);

-- -------------------------------------------------------------------

CREATE TABLE sessions
(
    id            VARCHAR(128)                       NOT NULL PRIMARY KEY,
    user_id       BIGINT UNSIGNED                    NULL,
    ip_address    VARCHAR(45)                        NULL,
    user_agent    VARCHAR(255)                       NULL,
    payload       LONGTEXT                           NOT NULL,
    last_activity INT UNSIGNED                       NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,

    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX idx_sessions_last_activity ON sessions (last_activity);
CREATE INDEX idx_sessions_user ON sessions (user_id);

-- -------------------------------------------------------------------

CREATE TABLE audit_logs
(
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED                    NULL,
    event       VARCHAR(100)                       NOT NULL,
    description VARCHAR(255)                       NULL,
    ip_address  VARCHAR(45)                        NULL,
    user_agent  VARCHAR(255)                       NULL,
    metadata    JSON                               NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,

    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE INDEX idx_audit_user_event ON audit_logs (user_id, event, created_at);

-- -------------------------------------------------------------------

CREATE TABLE clients
(
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150)                       NOT NULL,
    city       VARCHAR(100)                       NULL,
    state      CHAR(2)                            NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME                           NULL
);

CREATE INDEX idx_clients_name ON clients (name);

-- -------------------------------------------------------------------

CREATE TABLE orders
(
    id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number       VARCHAR(20)                        NOT NULL,
    client_id          BIGINT UNSIGNED                    NOT NULL,

    product_qty        INT UNSIGNED                       NULL,
    item_qty           INT UNSIGNED                       NULL,
    invoice_number     VARCHAR(20)                        NULL,
    order_date         DATE                               NULL,

    freight_type       ENUM('own_fleet', 'cif_carrier', 'fob_client') NULL,
    vehicle_type       VARCHAR(50)                        NULL,
    freight_value      DECIMAL(12, 2)                     NULL,

    loading_date       DATE                               NULL,
    delivery_date      DATE                               NULL,
    expected_delivery  DATE                               NULL,

    status             ENUM('in_production', 'awaiting_loading', 'in_transit', 'delivered', 'pending')
                                                           NOT NULL DEFAULT 'pending',
    source             ENUM('spreadsheet', 'system')      NOT NULL DEFAULT 'system',

    created_at         DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at         DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         DATETIME                           NULL,

    CONSTRAINT uq_orders_number UNIQUE (order_number),
    CONSTRAINT fk_orders_client FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE
);

CREATE INDEX idx_orders_status ON orders (status);
CREATE INDEX idx_orders_client ON orders (client_id);
CREATE INDEX idx_orders_date ON orders (order_date);

-- -------------------------------------------------------------------

CREATE TABLE imports
(
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT UNSIGNED                    NOT NULL,
    file_name      VARCHAR(255)                       NOT NULL,
    total_rows     INT UNSIGNED DEFAULT 0             NOT NULL,
    created_count  INT UNSIGNED DEFAULT 0             NOT NULL,
    updated_count  INT UNSIGNED DEFAULT 0             NOT NULL,
    error_count    INT UNSIGNED DEFAULT 0             NOT NULL,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,

    CONSTRAINT fk_imports_user FOREIGN KEY (user_id) REFERENCES users (id)
);