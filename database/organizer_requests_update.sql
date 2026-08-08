USE sportconnect;

CREATE TABLE IF NOT EXISTS organizer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    pending_user_id INT GENERATED ALWAYS AS (CASE WHEN status = 'pending' THEN user_id ELSE NULL END) STORED,
    UNIQUE KEY unique_pending_organizer_request (pending_user_id),
    INDEX idx_organizer_requests_status_created (status, created_at),
    CONSTRAINT fk_organizer_request_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_organizer_request_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
