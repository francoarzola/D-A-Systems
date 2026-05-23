<?php

declare(strict_types=1);

$pageTitle = isset($pageTitle) && is_string($pageTitle) ? $pageTitle : 'Sistema interno D&A Systems';
$pageHeading = isset($pageHeading) && is_string($pageHeading) ? $pageHeading : 'Sistema interno';
$userName = isset($userName) && is_string($userName) && $userName !== '' ? $userName : 'Usuario';
$content = isset($content) && is_string($content) ? $content : '';
$stylesheetPath = isset($stylesheetPath) && is_string($stylesheetPath) ? $stylesheetPath : 'assets/css/internal.css';
$activeNav = isset($activeNav) && is_string($activeNav) ? $activeNav : 'dashboard';

$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php', 'enabled' => true],
    ['key' => 'cotizaciones', 'label' => 'Cotizaciones', 'href' => 'cotizaciones.php', 'enabled' => true],
    ['key' => 'clientes', 'label' => 'Clientes', 'href' => 'clientes.php', 'enabled' => true],
    ['key' => 'atenciones', 'label' => 'Atenciones', 'href' => 'atenciones.php', 'enabled' => true],
    ['key' => 'configuracion', 'label' => 'Configuración', 'href' => 'configuracion.php', 'enabled' => true],
];

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

    <nav class="card" aria-label="Navegación interna" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:22px;align-items:center;">
      <?php foreach ($navItems as $item): ?>
        <?php
        $isActive = $activeNav === $item['key'];
        $isEnabled = (bool) $item['enabled'];
        $itemStyle = $isActive
            ? 'background:#1d75cf;color:#ffffff;border-color:#1d75cf;'
            : 'background:#ffffff;color:#1f2a44;border-color:rgba(17,34,64,0.16);';
        $disabledStyle = !$isEnabled ? 'opacity:0.55;cursor:not-allowed;' : '';
        ?>
        <a
          href="<?php echo $escape($item['href']); ?>"
          style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border:1px solid;border-radius:999px;text-decoration:none;font-weight:600;font-size:0.95rem;<?php echo $itemStyle . $disabledStyle; ?>"
          <?php echo $isActive ? 'aria-current="page"' : ''; ?>
          <?php echo !$isEnabled ? 'aria-disabled="true" tabindex="-1"' : ''; ?>
        >
          <?php echo $escape($item['label']); ?>
        </a>
      <?php endforeach; ?>

      <a
        href="logout.php"
        style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border:1px solid rgba(26,61,143,0.2);border-radius:999px;text-decoration:none;font-weight:600;font-size:0.95rem;background:#f3f6fb;color:#1a3d8f;"
      >
        Cerrar sesión
      </a>
    </nav>

    <?php echo $content; ?>

    <footer>
      <small>Panel de control interno protegido.</small>
    </footer>
  </div>
</body>
</html>
