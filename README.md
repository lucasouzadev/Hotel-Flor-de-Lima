# Hotel Flor de Lima

Um website completo para o Hotel Flor de Lima, oferecendo hospedagem, gastronomia e uma experiência única inspirada nas culturas eslava e japonesa.

## 🏨 Sobre o Projeto

O Hotel Flor de Lima é mais que um local de hospedagem - é uma experiência completa que combina conforto, gastronomia excepcional e um ambiente único. O projeto inclui um sistema completo de reservas, o renomado Bar Celina com carta de drinks exclusiva, e o jornal O CORVO para interação com hóspedes.

## ✨ Funcionalidades Principais

### 🏠 Página Inicial
- Apresentação elegante do hotel
- Showcase de quartos e comodidades
- Depoimentos de hóspedes e influencers
- Preview do jornal O CORVO
- Design responsivo e moderno

### 🔐 Sistema de Autenticação
- Registro de usuários com validação completa
- Login seguro com sessões
- Dashboard personalizado
- Gerenciamento de perfil
- Alteração de senha

### 🛏️ Sistema de Reservas
- Visualização de tipos de quartos
- Verificação de disponibilidade em tempo real
- Cálculo automático de preços
- Gestão de reservas pelo usuário
- Sistema de check-in/check-out

### 🍸 Bar Celina
- Carta de drinks inspirada nas culturas eslava e japonesa
- Categorias: Drinks Eslavos, Japoneses, Sem Álcool, Soft Drinks e Menu Infantil
- Sistema de carrinho interativo
- Recomendações do chef
- Pedidos com especificações detalhadas

### 📰 Jornal O CORVO
- Fórum do hotel com artigos e notícias
- Sistema de feedbacks com avaliação por estrelas
- Comentários de hóspedes
- Categorização de conteúdo
- Interação da comunidade

### 🛒 Sistema de Checkout
- Processo de checkout em 3 etapas
- Informações de cobrança
- Métodos de pagamento (Cartão, PIX, Dinheiro)
- Cálculo de taxas e totais
- Confirmação de pedidos

### 📊 Dashboard do Usuário
- Estatísticas pessoais
- Histórico de reservas e pedidos
- Atividade recente
- Ações rápidas
- Gerenciamento de perfil

## 🛠️ Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL
- **Estilização**: CSS Grid, Flexbox, Animações CSS
- **Icons**: Font Awesome
- **Fonts**: Google Fonts (Playfair Display, Open Sans)

## 📁 Estrutura do Projeto

```
FlorDeLima/
├── index.php                 # Página inicial
├── login.php                 # Página de login
├── register.php              # Página de registro
├── logout.php                # Logout
├── dashboard.php             # Dashboard do usuário
├── reservations.php          # Sistema de reservas
├── bar-celina.php           # Bar Celina
├── newspaper.php            # Jornal O CORVO
├── checkout.php             # Sistema de checkout
├── config/
│   └── database.php         # Configurações do banco
├── includes/
│   └── auth.php             # Sistema de autenticação
├── database/
│   └── schema.sql           # Schema do banco de dados
└── assets/
    ├── css/
    │   ├── style.css        # Estilos principais
    │   ├── auth.css         # Estilos de autenticação
    │   ├── bar.css          # Estilos do Bar Celina
    │   ├── reservations.css # Estilos de reservas
    │   ├── newspaper.css    # Estilos do jornal
    │   ├── checkout.css     # Estilos de checkout
    │   └── dashboard.css    # Estilos do dashboard
    ├── js/
    │   ├── script.js        # Scripts principais
    │   ├── auth.js          # Scripts de autenticação
    │   ├── bar.js           # Scripts do Bar Celina
    │   ├── reservations.js  # Scripts de reservas
    │   ├── newspaper.js     # Scripts do jornal
    │   ├── checkout.js      # Scripts de checkout
    │   └── dashboard.js     # Scripts do dashboard
    └── images/              # Imagens do projeto
```

## 🚀 Instalação

### Pré-requisitos
- XAMPP (Apache + MySQL + PHP)
- Navegador web moderno

### Passos de Instalação

1. **Clone ou baixe o projeto**
   ```bash
   # Coloque os arquivos na pasta htdocs do XAMPP
   # Exemplo: C:\xampp\htdocs\FlorDeLima\
   ```

2. **Configure o banco de dados**
   - Abra o phpMyAdmin (http://localhost/phpmyadmin)
   - Execute o arquivo `database/schema.sql` para criar o banco e tabelas
   - O banco será criado automaticamente com dados de exemplo

3. **Configure as credenciais do banco**
   - Edite `config/database.php` se necessário
   - Por padrão usa: host=localhost, user=root, password='', database=hotel_flor_de_lima

4. **Acesse o projeto**
   ```
   http://localhost/FlorDeLima/
   ```

## 📊 Banco de Dados

O projeto inclui as seguintes tabelas principais:

- **users**: Usuários do sistema
- **room_types**: Tipos de quartos disponíveis
- **rooms**: Quartos individuais
- **reservations**: Reservas de hospedagem
- **accommodations**: Check-ins e check-outs
- **drink_categories**: Categorias de drinks
- **drinks**: Cardápio de drinks do Bar Celina
- **bar_orders**: Pedidos do bar
- **bar_order_items**: Itens dos pedidos
- **leisure_areas**: Áreas de lazer
- **newspaper_articles**: Artigos do jornal
- **comments**: Comentários de usuários
- **feedbacks**: Avaliações de hóspedes
- **cart_items**: Itens do carrinho
- **promotions**: Promoções do hotel

## 🎨 Design e UX

### Características do Design
- **Tema**: Elegante e sofisticado com tons de marrom e dourado
- **Tipografia**: Playfair Display para títulos, Open Sans para textos
- **Layout**: Responsivo com CSS Grid e Flexbox
- **Animações**: Transições suaves e efeitos hover
- **Acessibilidade**: Contraste adequado e navegação por teclado

### Experiência do Usuário
- Interface intuitiva e fácil navegação
- Feedback visual para todas as ações
- Validação em tempo real de formulários
- Mensagens de erro claras e úteis
- Sistema de notificações não intrusivo

## 🔧 Funcionalidades Técnicas

### Segurança
- Validação de dados no frontend e backend
- Sanitização de inputs
- Proteção contra SQL injection com prepared statements
- Sessões seguras
- Hash de senhas com password_hash()

### Performance
- Carregamento lazy de imagens
- Animações otimizadas com CSS
- Debounce em buscas e validações
- Compressão de assets

### Responsividade
- Mobile-first design
- Breakpoints: 480px, 768px, 1024px
- Navegação adaptativa
- Formulários otimizados para mobile

## 📱 Compatibilidade

- **Navegadores**: Chrome, Firefox, Safari, Edge (versões recentes)
- **Dispositivos**: Desktop, Tablet, Mobile
- **Resoluções**: 320px até 1920px+

## 🚀 Funcionalidades Futuras

- Sistema de notificações por email
- Integração com gateway de pagamento
- Sistema de fidelidade
- App mobile
- Integração com redes sociais
- Sistema de reviews avançado
- Chat ao vivo com suporte

## 👥 Contribuição

Para contribuir com o projeto:

1. Fork o repositório
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto é propriedade do Hotel Flor de Lima. Todos os direitos reservados.

---

**Hotel Flor de Lima** - Uma experiência única onde tradições eslavas e japonesas se encontram. 🏨✨
