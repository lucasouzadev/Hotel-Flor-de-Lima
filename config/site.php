<?php
// Configurações do site
define('SITE_NAME', 'Hotel Flor de Lima');
define('SITE_TAGLINE', 'Uma experiência única de hospedagem e gastronomia');
define('SITE_DESCRIPTION', 'Hotel de luxo onde tradições eslavas e japonesas se encontram em Lima, PE');
define('SITE_URL', 'http://localhost/FlorDeLima');
define('SITE_EMAIL', 'contato@hotelflordeLima.com.br');
define('SITE_PHONE', '(81) 3456-7890');
define('SITE_ADDRESS', 'Rua das Flores, 123 - Centro, Lima - PE');

// Configurações de desenvolvimento
define('DEBUG_MODE', false);
define('SHOW_ERRORS', false);

// Configurações de sessão
define('SESSION_LIFETIME', 7200); // 2 horas

// Configurações de upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configurações de paginação
define('ITEMS_PER_PAGE', 10);
define('ARTICLES_PER_PAGE', 5);
define('FEEDBACKS_PER_PAGE', 5);

// Configurações de notificação
define('NOTIFICATION_DURATION', 5000); // 5 segundos

// Configurações de cache
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600); // 1 hora

// Configurações de segurança
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

// Configurações do Bar Celina
define('BAR_OPENING_HOURS', '18:00 - 02:00');
define('BAR_HAPPY_HOUR_START', '18:00');
define('BAR_HAPPY_HOUR_END', '20:00');
define('BAR_HAPPY_HOUR_DISCOUNT', 0.15); // 15% de desconto

// Configurações de reservas
define('RESERVATION_ADVANCE_DAYS', 30); // Máximo de dias de antecedência
define('RESERVATION_CANCELLATION_HOURS', 24); // Horas para cancelamento

// Configurações de feedback
define('FEEDBACK_AUTO_APPROVE', false); // Aprovação manual de feedbacks
define('FEEDBACK_MIN_LENGTH', 10);
define('FEEDBACK_MAX_LENGTH', 500);

// Configurações de comentários
define('COMMENT_AUTO_APPROVE', false); // Aprovação manual de comentários
define('COMMENT_MIN_LENGTH', 10);
define('COMMENT_MAX_LENGTH', 300);

// Configurações de API
define('API_RATE_LIMIT', 100); // Requisições por hora por IP
define('API_TIMEOUT', 30); // Timeout em segundos

// Configurações de email (futuro)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', SITE_EMAIL);
define('SMTP_FROM_NAME', SITE_NAME);

// Configurações de redes sociais
define('FACEBOOK_URL', '');
define('INSTAGRAM_URL', '');
define('TWITTER_URL', '');
define('YOUTUBE_URL', '');

// Configurações de analytics (futuro)
define('GA_TRACKING_ID', '');

// Configurações de backup
define('BACKUP_ENABLED', false);
define('BACKUP_FREQUENCY', 'daily');
define('BACKUP_RETENTION_DAYS', 30);
?>
