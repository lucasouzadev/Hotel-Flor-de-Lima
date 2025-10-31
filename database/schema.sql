-- Banco de dados para o Hotel Flor de Lima
-- Versão convertida para PostgreSQL

-- ============================================================================
-- CONFIGURAÇÃO INICIAL
-- ============================================================================

-- Criar banco de dados (execute separadamente se necessário)
-- CREATE DATABASE hotel_flor_de_lima WITH ENCODING 'UTF8';
-- \c hotel_flor_de_lima;

-- ============================================================================
-- TIPOS ENUM
-- ============================================================================

CREATE TYPE user_status AS ENUM ('active', 'inactive', 'suspended');
CREATE TYPE room_status AS ENUM ('available', 'occupied', 'maintenance', 'cleaning');
CREATE TYPE reservation_status AS ENUM ('pending', 'confirmed', 'cancelled', 'completed');
CREATE TYPE accommodation_status AS ENUM ('checked_in', 'checked_out', 'no_show');
CREATE TYPE order_status AS ENUM ('pending', 'preparing', 'ready', 'served', 'cancelled');
CREATE TYPE leisure_status AS ENUM ('available', 'maintenance', 'closed');
CREATE TYPE leisure_reservation_status AS ENUM ('pending', 'confirmed', 'cancelled');
CREATE TYPE article_status AS ENUM ('draft', 'published', 'archived');
CREATE TYPE comment_status AS ENUM ('pending', 'approved', 'rejected');
CREATE TYPE feedback_category AS ENUM ('accommodation', 'service', 'food', 'facilities', 'general');
CREATE TYPE cart_item_type AS ENUM ('room', 'drink', 'leisure', 'promotion');
CREATE TYPE discount_type AS ENUM ('percentage', 'fixed_amount');
CREATE TYPE promotion_status AS ENUM ('active', 'inactive', 'expired');

-- ============================================================================
-- FUNÇÕES AUXILIARES
-- ============================================================================

-- Função para atualizar updated_at automaticamente
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================================================
-- TABELAS PRINCIPAIS
-- ============================================================================

-- Tabela de usuários
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    birth_date DATE,
    document VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status user_status DEFAULT 'active'
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);

-- Trigger para updated_at
CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Tabela de tipos de quartos
CREATE TABLE room_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    max_occupancy INTEGER NOT NULL,
    base_price NUMERIC(10,2) NOT NULL,
    amenities JSONB,
    images JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_room_types_name ON room_types(name);

-- Tabela de quartos
CREATE TABLE rooms (
    id SERIAL PRIMARY KEY,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type_id INTEGER NOT NULL,
    floor INTEGER NOT NULL,
    status room_status DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_room_type FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
);

CREATE INDEX idx_rooms_room_number ON rooms(room_number);
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_rooms_room_type ON rooms(room_type_id);

-- Tabela de reservas
CREATE TABLE reservations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INTEGER NOT NULL,
    total_amount NUMERIC(10,2) NOT NULL,
    status reservation_status DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reservation_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE INDEX idx_reservations_user_id ON reservations(user_id);
CREATE INDEX idx_reservations_room_id ON reservations(room_id);
CREATE INDEX idx_reservations_check_in ON reservations(check_in);
CREATE INDEX idx_reservations_check_out ON reservations(check_out);
CREATE INDEX idx_reservations_status ON reservations(status);

-- Trigger para updated_at
CREATE TRIGGER update_reservations_updated_at
    BEFORE UPDATE ON reservations
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Tabela de hospedagens
CREATE TABLE accommodations (
    id SERIAL PRIMARY KEY,
    reservation_id INTEGER NOT NULL,
    check_in_time TIMESTAMP,
    check_out_time TIMESTAMP,
    status accommodation_status DEFAULT 'checked_in',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_accommodation_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
);

CREATE INDEX idx_accommodations_reservation_id ON accommodations(reservation_id);
CREATE INDEX idx_accommodations_status ON accommodations(status);

-- Tabela de categorias de drinks
CREATE TABLE drink_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_drink_categories_name ON drink_categories(name);

-- Tabela de drinks
CREATE TABLE drinks (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INTEGER NOT NULL,
    description TEXT,
    ingredients TEXT,
    price NUMERIC(8,2) NOT NULL,
    alcohol_content NUMERIC(3,1) DEFAULT 0,
    is_alcoholic BOOLEAN DEFAULT TRUE,
    is_available BOOLEAN DEFAULT TRUE,
    image VARCHAR(255),
    preparation_time INTEGER DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_drink_category FOREIGN KEY (category_id) REFERENCES drink_categories(id) ON DELETE CASCADE
);

CREATE INDEX idx_drinks_category_id ON drinks(category_id);
CREATE INDEX idx_drinks_is_available ON drinks(is_available);
CREATE INDEX idx_drinks_is_alcoholic ON drinks(is_alcoholic);

-- Tabela de pedidos do bar
CREATE TABLE bar_orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    table_number VARCHAR(10),
    status order_status DEFAULT 'pending',
    total_amount NUMERIC(10,2) NOT NULL,
    order_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    CONSTRAINT fk_bar_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bar_orders_user_id ON bar_orders(user_id);
CREATE INDEX idx_bar_orders_status ON bar_orders(status);
CREATE INDEX idx_bar_orders_order_time ON bar_orders(order_time);

-- Tabela de itens do pedido
CREATE TABLE bar_order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    drink_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price NUMERIC(8,2) NOT NULL,
    total_price NUMERIC(8,2) NOT NULL,
    special_instructions TEXT,
    CONSTRAINT fk_order_item_order FOREIGN KEY (order_id) REFERENCES bar_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_item_drink FOREIGN KEY (drink_id) REFERENCES drinks(id) ON DELETE CASCADE
);

CREATE INDEX idx_bar_order_items_order_id ON bar_order_items(order_id);
CREATE INDEX idx_bar_order_items_drink_id ON bar_order_items(drink_id);

-- Tabela de áreas de lazer
CREATE TABLE leisure_areas (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INTEGER,
    amenities JSONB,
    operating_hours JSONB,
    images JSONB,
    status leisure_status DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_leisure_areas_status ON leisure_areas(status);

-- Tabela de reservas de áreas de lazer
CREATE TABLE leisure_reservations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    area_id INTEGER NOT NULL,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    guests INTEGER NOT NULL,
    status leisure_reservation_status DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_leisure_reservation_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_leisure_reservation_area FOREIGN KEY (area_id) REFERENCES leisure_areas(id) ON DELETE CASCADE
);

CREATE INDEX idx_leisure_reservations_user_id ON leisure_reservations(user_id);
CREATE INDEX idx_leisure_reservations_area_id ON leisure_reservations(area_id);
CREATE INDEX idx_leisure_reservations_reservation_date ON leisure_reservations(reservation_date);
CREATE INDEX idx_leisure_reservations_status ON leisure_reservations(status);

-- Tabela de categorias do jornal
CREATE TABLE newspaper_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_newspaper_categories_name ON newspaper_categories(name);

-- Tabela de artigos do jornal
CREATE TABLE newspaper_articles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category_id INTEGER NOT NULL,
    author_id INTEGER NOT NULL,
    featured_image VARCHAR(255),
    status article_status DEFAULT 'draft',
    published_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_article_category FOREIGN KEY (category_id) REFERENCES newspaper_categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_article_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_newspaper_articles_category_id ON newspaper_articles(category_id);
CREATE INDEX idx_newspaper_articles_author_id ON newspaper_articles(author_id);
CREATE INDEX idx_newspaper_articles_status ON newspaper_articles(status);
CREATE INDEX idx_newspaper_articles_published_at ON newspaper_articles(published_at);

-- Trigger para updated_at
CREATE TRIGGER update_newspaper_articles_updated_at
    BEFORE UPDATE ON newspaper_articles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Tabela de comentários
CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    article_id INTEGER,
    user_id INTEGER NOT NULL,
    parent_id INTEGER,
    content TEXT NOT NULL,
    status comment_status DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_article FOREIGN KEY (article_id) REFERENCES newspaper_articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);

CREATE INDEX idx_comments_article_id ON comments(article_id);
CREATE INDEX idx_comments_user_id ON comments(user_id);
CREATE INDEX idx_comments_parent_id ON comments(parent_id);
CREATE INDEX idx_comments_status ON comments(status);

-- Tabela de feedbacks
CREATE TABLE feedbacks (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    reservation_id INTEGER,
    rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    content TEXT NOT NULL,
    category feedback_category DEFAULT 'general',
    status comment_status DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
);

CREATE INDEX idx_feedbacks_user_id ON feedbacks(user_id);
CREATE INDEX idx_feedbacks_reservation_id ON feedbacks(reservation_id);
CREATE INDEX idx_feedbacks_rating ON feedbacks(rating);
CREATE INDEX idx_feedbacks_status ON feedbacks(status);

-- Tabela de carrinho
CREATE TABLE cart_items (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    item_type cart_item_type NOT NULL,
    item_id INTEGER NOT NULL,
    quantity INTEGER DEFAULT 1,
    price NUMERIC(10,2) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    CONSTRAINT fk_cart_item_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_cart_items_user_id ON cart_items(user_id);
CREATE INDEX idx_cart_items_expires_at ON cart_items(expires_at);

-- Tabela de promoções
CREATE TABLE promotions (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    discount_type discount_type NOT NULL,
    discount_value NUMERIC(10,2) NOT NULL,
    min_stay INTEGER DEFAULT 1,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    max_uses INTEGER,
    current_uses INTEGER DEFAULT 0,
    status promotion_status DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_promotions_status ON promotions(status);
CREATE INDEX idx_promotions_valid_from ON promotions(valid_from);
CREATE INDEX idx_promotions_valid_until ON promotions(valid_until);

-- Tabela de configurações do sistema
CREATE TABLE system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_system_settings_setting_key ON system_settings(setting_key);

-- Trigger para updated_at
CREATE TRIGGER update_system_settings_updated_at
    BEFORE UPDATE ON system_settings
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- DADOS INICIAIS
-- ============================================================================

-- Tipos de quartos
INSERT INTO room_types (name, description, max_occupancy, base_price, amenities, images) VALUES
('Standard', 'Quarto confortável com todos os serviços essenciais', 2, 180.00, 
 '["Wi-Fi gratuito", "TV Smart", "Banheiro privativo", "Ar condicionado", "Cofre"]'::jsonb,
 '["room-standard-1.jpg", "room-standard-2.jpg"]'::jsonb),
 
('Deluxe', 'Quarto espaçoso com vista privilegiada e comodidades extras', 2, 320.00,
 '["Wi-Fi gratuito", "TV Smart", "Banheiro privativo", "Ar condicionado", "Cofre", "Mini bar", "Vista privilegiada", "Área de estar"]'::jsonb,
 '["room-deluxe-1.jpg", "room-deluxe-2.jpg"]'::jsonb),
 
('Suite Presidencial', 'Suíte completa com máximo conforto e sofisticação', 4, 580.00,
 '["Wi-Fi gratuito", "TV Smart", "Banheiro privativo", "Ar condicionado", "Cofre", "Mini bar", "Vista panorâmica", "Salão privativo", "Serviço de concierge"]'::jsonb,
 '["room-suite-1.jpg", "room-suite-2.jpg"]'::jsonb);

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
 '["Cadeiras de praia", "Guarda-sóis", "Bar de piscina", "Vestiários"]'::jsonb,
 '{"open": "06:00", "close": "22:00", "days": "todos"}'::jsonb,
 '["pool-1.jpg", "pool-2.jpg"]'::jsonb, 'available'),
 
('Spa & Wellness', 'Centro de bem-estar com tratamentos relaxantes', 20,
 '["Massagens", "Sauna", "Banho turco", "Tratamentos faciais"]'::jsonb,
 '{"open": "08:00", "close": "20:00", "days": "todos"}'::jsonb,
 '["spa-1.jpg", "spa-2.jpg"]'::jsonb, 'available'),
 
('Academia', 'Academia moderna com equipamentos de última geração', 30,
 '["Equipamentos cardio", "Musculação", "Personal trainer", "Aulas em grupo"]'::jsonb,
 '{"open": "05:00", "close": "23:00", "days": "todos"}'::jsonb,
 '["gym-1.jpg", "gym-2.jpg"]'::jsonb, 'available'),
 
('Jardim Zen', 'Jardim japonês para meditação e relaxamento', 15,
 '["Área de meditação", "Fontes", "Plantas japonesas", "Bancos"]'::jsonb,
 '{"open": "06:00", "close": "20:00", "days": "todos"}'::jsonb,
 '["zen-garden-1.jpg", "zen-garden-2.jpg"]'::jsonb, 'available');

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
