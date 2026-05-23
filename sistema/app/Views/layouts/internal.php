<?php

declare(strict_types=1);

$pageTitle = isset($pageTitle) && is_string($pageTitle) ? $pageTitle : 'Sistema interno D&A Systems';
$pageHeading = isset($pageHeading) && is_string($pageHeading) ? $pageHeading : 'Sistema interno';
$userName = isset($userName) && is_string($userName) && $userName !== '' ? $userName : 'Usuario';
$content = isset($content) && is_string($content) ? $content : '';
$stylesheetPath = isset($stylesheetPath) && is_string($stylesheetPath) ? $stylesheetPath : 'assets/css/internal.css';

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $escape($pageTitle); ?></title>
  <link rel="stylesheet" href="<?php echo $escape($stylesheetPath); ?>">
</head>
<body>
  <div class="container">
    <section class="header">
      <div class="brand">
        <span class="brand-dot"></span>
        D&A Systems
      </div>
      <h1 class="title"><?php echo $escape($pageHeading); ?></h1>
      <p class="subtitle">Bienvenido, <?php echo $escape($userName); ?>.</p>
    </section>

    <?php echo $content; ?>

    <div class="button-wrapper" style="margin-top:22px;">
      <a href="logout.php" class="button-disabled" role="button">Cerrar sesión</a>
    </div>

    <footer>
      <small>Panel de control interno protegido.</small>
    </footer>
  </div>
</body>
</html>
