-- Banco de dados para o Hotel Flor de Lima
-- Versão corrigida para compatibilidade com MySQL

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS hotel_flor_de_lima DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_flor_de_lima;

-- Tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    document VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tipos de quartos
CREATE TABLE room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    max_occupancy INT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    amenities JSON,
    images JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de quartos
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    room_type_id INT NOT NULL,
    floor INT NOT NULL,
    status ENUM('available', 'occupied', 'maintenance', 'cleaning') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de reservas
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de hospedagens
CREATE TABLE accommodations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    check_in_time TIMESTAMP NULL DEFAULT NULL,
    check_out_time TIMESTAMP NULL DEFAULT NULL,
    status ENUM('checked_in', 'checked_out', 'no_show') DEFAULT 'checked_in',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de categorias de drinks
CREATE TABLE drink_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de drinks
CREATE TABLE drinks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    ingredients TEXT,
    price DECIMAL(8,2) NOT NULL,
    alcohol_content DECIMAL(3,1) DEFAULT 0,
    is_alcoholic BOOLEAN DEFAULT TRUE,
    is_available BOOLEAN DEFAULT TRUE,
    image VARCHAR(255),
    preparation_time INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES drink_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de pedidos do bar
CREATE TABLE bar_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    table_number VARCHAR(10),
    status ENUM('pending', 'preparing', 'ready', 'served', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    order_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens do pedido
CREATE TABLE bar_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    drink_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(8,2) NOT NULL,
    total_price DECIMAL(8,2) NOT NULL,
    special_instructions TEXT,
    FOREIGN KEY (order_id) REFERENCES bar_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (drink_id) REFERENCES drinks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de áreas de lazer
CREATE TABLE leisure_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INT,
    amenities JSON,
    operating_hours JSON,
    images JSON,
    status ENUM('available', 'maintenance', 'closed') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de reservas de áreas de lazer
CREATE TABLE leisure_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    area_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    guests INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES leisure_areas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de categorias do jornal
CREATE TABLE newspaper_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de artigos do jornal
CREATE TABLE newspaper_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category_id INT NOT NULL,
    author_id INT NOT NULL,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES newspaper_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de comentários
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT,
    user_id INT NOT NULL,
    parent_id INT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES newspaper_articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de feedbacks
CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reservation_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    content TEXT NOT NULL,
    category ENUM('accommodation', 'service', 'food', 'facilities', 'general') DEFAULT 'general',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de carrinho
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('room', 'drink', 'leisure', 'promotion') NOT NULL,
    item_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de promoções
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_stay INT DEFAULT 1,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    max_uses INT,
    current_uses INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações do sistema
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados iniciais

-- Tipos de quartos
INSERT INTO room_types (name, description, max_occupancy, base_price, amenities, images) VALUES
('Standard', 'Quarto confortável com todos os serviços essenciais', 2, 180.00, 
 '["Wi-Fi gratuito", "TV Smart", "Banheiro privativo", "Ar condicionado", "Cofre"]',
 '["room-standard-1.jpg", "room-standard-2.jpg"]'),
 
('Deluxe', 'Quarto espaçoso com vista privilegiada e comodidades extras', 2, 320.00,
 '["Wi-Fi gratuito", "TV Smart", "Banheiro privativo", "Ar condicionado", "Cofre", "Mini bar", "Vista privilegiada", "Área de estar"]',
 '["room-deluxe-1.jpg", "room-deluxe-2.jpg"]'),
 
('Suite Presidencial', 'Suíte completa com máximo conforto e sofisticação', 4, 580.00,
 '["Wi-Fi gratuito", "TV Smart", "Banheiro privativo", "Ar condicionado", "Cofre", "Mini bar", "Vista panorâmica", "Salão privativo", "Serviço de concierge"]',
 '["room-suite-1.jpg", "room-suite-2.jpg"]');

-- Quartos
INSERT INTO rooms (room_number, room_type_id, floor) VALUES
('101', 1, 1), ('102', 1, 1), ('103', 1, 1), ('104', 1, 1), ('105', 1, 1),
('201', 2, 2), ('202', 2, 2), ('203', 2, 2), ('204', 2, 2),
('301', 3, 3), ('302', 3, 3);

-- Categorias de drinks
INSERT INTO drink_categories (name, description, image) VALUES
('Drinks Eslavos', 'Bebidas tradicionais da cultura eslava', 'slavic-drinks.jpg'),
('Drinks Japoneses', 'Bebidas inspiradas na cultura japonesa', 'japanese-drinks.jpg'),
('Drinks Sem Álcool', 'Bebidas refrescantes sem álcool', 'non-alcoholic.jpg'),
('Soft Drinks & Shakes', 'Bebidas suaves e shakes cremosos', 'soft-drinks.jpg'),
('Menu Infantil', 'Bebidas especiais para crianças', 'kids-menu.jpg');

-- Drinks
INSERT INTO drinks (name, category_id, description, ingredients, price, alcohol_content, is_alcoholic, image) VALUES
-- Drinks Eslavos
('Vodka Tradicional', 1, 'Vodka premium com toque especial', 'Vodka premium, gelo, limão', 25.00, 40.0, TRUE, 'vodka-tradicional.jpg'),
('Borscht Cocktail', 1, 'Drink inspirado na sopa tradicional', 'Vodka, beterraba, limão, especiarias', 28.00, 35.0, TRUE, 'borscht-cocktail.jpg'),
('Slavic Mule', 1, 'Moscow Mule com toque eslavo', 'Vodka, gengibre, limão, água tônica', 22.00, 38.0, TRUE, 'slavic-mule.jpg'),

-- Drinks Japoneses
('Sakura Sour', 2, 'Drink floral inspirado na cerejeira', 'Sake, licor de cereja, limão, água de rosas', 32.00, 25.0, TRUE, 'sakura-sour.jpg'),
('Matcha Martini', 2, 'Martini com chá verde', 'Vodka, licor de matcha, creme de coco', 30.00, 30.0, TRUE, 'matcha-martini.jpg'),
('Wasabi Bloody Mary', 2, 'Bloody Mary com toque de wasabi', 'Vodka, suco de tomate, wasabi, especiarias', 26.00, 35.0, TRUE, 'wasabi-bloody-mary.jpg'),

-- Drinks Sem Álcool
('Limonada de Lavanda', 3, 'Refrescante limonada com lavanda', 'Limão, lavanda, água, gelo', 12.00, 0.0, FALSE, 'lavender-lemonade.jpg'),
('Chá Gelado de Jasmim', 3, 'Chá de jasmim gelado e refrescante', 'Chá de jasmim, gelo, mel', 10.00, 0.0, FALSE, 'jasmine-iced-tea.jpg'),
('Água Infusa de Pepino', 3, 'Água refrescante com pepino e menta', 'Água, pepino, menta, gelo', 8.00, 0.0, FALSE, 'cucumber-water.jpg'),

-- Soft Drinks & Shakes
('Milkshake de Vanilla', 4, 'Milkshake cremoso de baunilha', 'Sorvete de baunilha, leite, chantilly', 15.00, 0.0, FALSE, 'vanilla-milkshake.jpg'),
('Smoothie Tropical', 4, 'Smoothie com frutas tropicais', 'Manga, abacaxi, banana, leite de coco', 18.00, 0.0, FALSE, 'tropical-smoothie.jpg'),
('Chocolate Quente', 4, 'Chocolate quente cremoso', 'Chocolate, leite, chantilly, marshmallows', 12.00, 0.0, FALSE, 'hot-chocolate.jpg'),

-- Menu Infantil
('Shake de Morango', 5, 'Shake doce de morango para crianças', 'Sorvete de morango, leite, açúcar', 10.00, 0.0, FALSE, 'strawberry-shake.jpg'),
('Suco de Laranja Natural', 5, 'Suco fresco de laranja', 'Laranjas frescas, gelo', 8.00, 0.0, FALSE, 'orange-juice.jpg'),
('Água com Gás e Xarope', 5, 'Água com gás e xarope de fruta', 'Água com gás, xarope de fruta', 6.00, 0.0, FALSE, 'sparkling-water-syrup.jpg');

-- Áreas de lazer
INSERT INTO leisure_areas (name, description, capacity, amenities, operating_hours, images, status) VALUES
('Piscina Principal', 'Piscina aquecida com vista panorâmica', 50, 
 '["Cadeiras de praia", "Guarda-sóis", "Bar de piscina", "Vestiários"]',
 '{"open": "06:00", "close": "22:00", "days": "todos"}',
 '["pool-1.jpg", "pool-2.jpg"]', 'available'),
 
('Spa & Wellness', 'Centro de bem-estar com tratamentos relaxantes', 20,
 '["Massagens", "Sauna", "Banho turco", "Tratamentos faciais"]',
 '{"open": "08:00", "close": "20:00", "days": "todos"}',
 '["spa-1.jpg", "spa-2.jpg"]', 'available'),
 
('Academia', 'Academia moderna com equipamentos de última geração', 30,
 '["Equipamentos cardio", "Musculação", "Personal trainer", "Aulas em grupo"]',
 '{"open": "05:00", "close": "23:00", "days": "todos"}',
 '["gym-1.jpg", "gym-2.jpg"]', 'available'),
 
('Jardim Zen', 'Jardim japonês para meditação e relaxamento', 15,
 '["Área de meditação", "Fontes", "Plantas japonesas", "Bancos"]',
 '{"open": "06:00", "close": "20:00", "days": "todos"}',
 '["zen-garden-1.jpg", "zen-garden-2.jpg"]', 'available');

-- Categorias do jornal
INSERT INTO newspaper_categories (name, description) VALUES
('Notícias do Hotel', 'Atualizações e novidades do hotel'),
('Gastronomia', 'Notícias sobre comida e bebida'),
('Turismo', 'Dicas e informações sobre turismo'),
('Feedback', 'Avaliações e comentários de hóspedes'),
('Promoções', 'Ofertas especiais e promoções');

-- Configurações do sistema
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('hotel_name', 'Hotel Flor de Lima', 'Nome do hotel'),
('hotel_email', 'contato@hotelflordeLima.com.br', 'Email principal do hotel'),
('hotel_phone', '(81) 3456-7890', 'Telefone principal do hotel'),
('check_in_time', '14:00', 'Horário padrão de check-in'),
('check_out_time', '12:00', 'Horário padrão de check-out'),
('max_reservation_days', '30', 'Máximo de dias para reserva antecipada'),
('min_reservation_days', '1', 'Mínimo de dias para reserva'),
('cancellation_hours', '24', 'Horas antes do check-in para cancelamento sem taxa');

COMMIT;
