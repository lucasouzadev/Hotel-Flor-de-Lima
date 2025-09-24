<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - Hotel Flor de Lima</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        .error-content h1 {
            font-size: 6rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .error-content h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .error-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-primary {
            background: #FFD700;
            color: #8B4513;
        }
        .btn-primary:hover {
            background: #FFF8DC;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        .btn-secondary:hover {
            background: white;
            color: #8B4513;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <h1>404</h1>
            <h2>Página não encontrada</h2>
            <p>Desculpe, a página que você está procurando não existe ou foi movida. Que tal explorar nosso hotel incrível?</p>
            
            <div class="error-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Voltar ao Início
                </a>
                <a href="reservations.php" class="btn btn-secondary">
                    <i class="fas fa-bed"></i>
                    Fazer Reserva
                </a>
                <a href="bar-celina.php" class="btn btn-secondary">
                    <i class="fas fa-cocktail"></i>
                    Bar Celina
                </a>
            </div>
        </div>
    </div>
</body>
</html>
