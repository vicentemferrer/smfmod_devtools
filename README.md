# Git Workflow Manager for SMF 2.1

**Git Workflow Manager (GWM)** is a modern development workflow tool for Simple Machines Forum (SMF) 2.1. It bridges the gap between modern Git-based development (migrations, version control) and SMF's traditional Package Manager.

---

## 🇬🇧 English Documentation

### Introduction
GWM replaces the traditional manual editing of files or complex XML installers during development. Instead, you write PHP classes called **Migrations**.

- **In Development:** You apply or revert changes instantly from the Admin Panel.
- **For Production (Coming Soon):** You can generate a standard SMF Package (ZIP) from your migration, which can be installed on any SMF forum using the native Package Manager, meaning GWM doesn't need to be installed on production servers.

### Installation
1. Upload the `Sources/GitWorkflowManager`, `Themes`, and `languages` folders to your SMF installation.
2. Upload `install_gwm.php` to the root directory (where `SSI.php` is located).
3. Run `install_gwm.php` via your browser or CLI.
4. Delete `install_gwm.php` for security.
5. Go to **Admin > Maintenance > Git Workflow Manager** to see the dashboard.

### Creating a Migration
Create a PHP file in `gwm_migrations/` (e.g., `2023_10_01_MyFeature.php`).

```php
<?php

use SMF\Mods\GitWorkflowManager\AbstractMigration;

class Migration_2023_10_01_MyFeature extends AbstractMigration
{
    public function up()
    {
        // Create a table
        $this->create_table('{db_prefix}my_table', [
            ['name' => 'id', 'type' => 'int', 'auto' => true],
            ['name' => 'name', 'type' => 'varchar', 'size' => 255],
        ], [['type' => 'primary', 'columns' => ['id']]]);

        // Add a hook
        $this->add_hook('integrate_pre_include', '$sourcedir/MyMod.php');
    }

    public function down()
    {
        // Revert everything
        $this->remove_hook('integrate_pre_include', '$sourcedir/MyMod.php');
        $this->drop_table('{db_prefix}my_table');
    }
}
```

---

## 🇪🇸 Documentación en Español

### Introducción
GWM reemplaza la edición manual de archivos o los complejos instaladores XML durante el desarrollo. En su lugar, escribes clases PHP llamadas **Migraciones**.

- **En Desarrollo:** Aplicas o reviertes cambios al instante desde el Panel de Administración.
- **Para Producción (Próximamente):** Puedes generar un Paquete SMF estándar (ZIP) a partir de tu migración, el cual puede instalarse en cualquier foro SMF usando el Gestor de Paquetes nativo.

### Instalación
1. Sube las carpetas `Sources/GitWorkflowManager`, `Themes`, y `languages` a tu instalación.
2. Sube `install_gwm.php` a la raíz.
3. Ejecuta `install_gwm.php` desde tu navegador o línea de comandos.
4. Borra `install_gwm.php`.
5. Ve a **Admin > Mantenimiento > Git Workflow Manager**.
