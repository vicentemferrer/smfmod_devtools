# DevFlow for SMF 2.1

**DevFlow** is a modern development workflow tool for Simple Machines Forum (SMF) 2.1. It brings the power of database migrations and hooks management to your fingertips, allowing you to version control your forum's modifications and easily package them for distribution.

---

## 🇬🇧 English Documentation

### Introduction
DevFlow replaces the traditional manual editing of files or complex XML installers during development. Instead, you write PHP classes called **Migrations**.

- **In Development:** You apply or revert changes instantly from the Admin Panel.
- **For Production:** You can generate a standard SMF Package (ZIP) from your migration, which can be installed on any SMF forum using the native Package Manager.

### Installation
1. Upload the `Sources/DevFlow`, `Themes`, and `languages` folders to your SMF installation.
2. Upload `install_devflow.php` to the root directory (where `SSI.php` is located).
3. Run `install_devflow.php` via your browser or CLI.
4. Delete `install_devflow.php` for security.
5. Go to **Admin > Maintenance > DevFlow** to see the dashboard.

### Creating a Migration
Create a PHP file in `devflow_migrations/` (e.g., `2023_10_01_MyFeature.php`).

```php
<?php

use SMF\Mods\DevFlow\AbstractMigration;

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

### Key Methods
- `create_table($name, $columns, $indexes)`
- `drop_table($name)`
- `add_column($table, $col_info)`
- `add_hook($hook, $function, $file)`
- `update_settings($settings)`

---

## 🇪🇸 Documentación en Español

### Introducción
DevFlow reemplaza la edición manual de archivos o los complejos instaladores XML durante el desarrollo. En su lugar, escribes clases PHP llamadas **Migraciones**.

- **En Desarrollo:** Aplicas o reviertes cambios al instante desde el Panel de Administración.
- **Para Producción:** Puedes generar un Paquete SMF estándar (ZIP) a partir de tu migración, el cual puede instalarse en cualquier foro SMF usando el Gestor de Paquetes nativo.

### Instalación
1. Sube las carpetas `Sources/DevFlow`, `Themes`, y `languages` a tu instalación de SMF.
2. Sube el archivo `install_devflow.php` a la raíz (donde está `SSI.php`).
3. Ejecuta `install_devflow.php` desde tu navegador o línea de comandos.
4. Borra `install_devflow.php` por seguridad.
5. Ve a **Admin > Mantenimiento > DevFlow** para ver el panel.

### Creando una Migración
Crea un archivo PHP en `devflow_migrations/` (ej: `2023_10_01_MiFuncion.php`).

```php
<?php

use SMF\Mods\DevFlow\AbstractMigration;

class Migration_2023_10_01_MiFuncion extends AbstractMigration
{
    public function up()
    {
        // Crear una tabla
        $this->create_table('{db_prefix}mi_tabla', [
            ['name' => 'id', 'type' => 'int', 'auto' => true],
            ['name' => 'nombre', 'type' => 'varchar', 'size' => 255],
        ], [['type' => 'primary', 'columns' => ['id']]]);

        // Añadir un hook
        $this->add_hook('integrate_pre_include', '$sourcedir/MiMod.php');
    }

    public function down()
    {
        // Revertir todo
        $this->remove_hook('integrate_pre_include', '$sourcedir/MiMod.php');
        $this->drop_table('{db_prefix}mi_tabla');
    }
}
```

### Métodos Principales
- `create_table($nombre, $columnas, $indices)`
- `drop_table($nombre)`
- `add_column($tabla, $info_columna)`
- `add_hook($hook, $funcion, $archivo)`
- `update_settings($configuracion)`
