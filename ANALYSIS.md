# Análisis del Mod "Developer Tools" para SMF

**Resumen Ejecutivo:**
Este mod, llamado "Developer Tools" (Herramientas de Desarrollador), está diseñado para facilitar la creación y gestión de modificaciones para Simple Machines Forum (SMF) 2.1.x. Su objetivo principal es permitir a los desarrolladores trabajar con mods basados en hooks (ganchos) sin necesidad de modificar archivos del núcleo, ofreciendo herramientas para reinstalar hooks, sincronizar archivos entre el entorno de desarrollo y la instalación en vivo, y empaquetar el mod para su distribución.

## 1. Funcionamiento Esperado (Una vez desplegado)

Una vez instalado, el mod añade una nueva acción al foro accesible mediante `index.php?action=devtools`. Esta interfaz está restringida a administradores y se divide en tres áreas principales:

*   **Paquetes (Packages):**
    *   Muestra una lista de los paquetes instalados que no están comprimidos (es decir, carpetas en el directorio `Packages/`).
    *   **Reinstalar Hooks:** Permite volver a ejecutar las instrucciones de instalación de hooks del archivo `package-info.xml` sin reinstalar todo el paquete. Útil cuando se añaden nuevos hooks durante el desarrollo.
    *   **Desinstalar Hooks:** Elimina los hooks registrados por el paquete.
    *   **Sincronizar (Sync In/Out):**
        *   *Sync In:* Copia los archivos desde la carpeta del paquete (en `Packages/tucarpeta`) hacia los directorios de SMF (`Sources/`, `Themes/`, etc.).
        *   *Sync Out:* Copia los archivos modificados en la instalación de SMF de vuelta a la carpeta del paquete. Esto es crucial para desarrolladores que editan archivos en la instalación en vivo y quieren guardar los cambios en su repositorio.

*   **Hooks:**
    *   Muestra un listado de **todos** los hooks de integración registrados en el sistema.
    *   Permite filtrar por nombre del hook, función o archivo.
    *   Ofrece controles para **activar/desactivar** hooks individualmente, editar sus parámetros (función, archivo incluido) o eliminarlos.
    *   Permite **añadir nuevos hooks** manualmente a la base de datos para pruebas rápidas.

*   **Archivos (Files):**
    *   Permite generar un archivo distribuible (`.zip` o `.tgz`) del paquete.
    *   Lee la configuración `<devtools>` en `package-info.xml` para determinar exclusiones de archivos (como `.git`, `.github`, etc.) y el nombre del paquete final.

## 2. Estructura de Archivos y Clases

El mod sigue una estructura orientada a objetos, típica de SMF 2.1.

### Archivos Principales

*   **`DevTools.php`**:
    *   **Clase:** `DevTools`
    *   **Propósito:** Es el controlador principal. Se encarga de inicializar el entorno, verificar permisos de administrador y enrutar las peticiones a los sub-controladores (`Packages`, `Hooks`, `Files`).
    *   **Funcionalidad:**
        *   Define los hooks de integración del propio mod (`integrate_actions`, `integrate_admin_areas`, etc.).
        *   Carga los archivos de idioma y plantillas necesarios.
        *   Maneja la lógica AJAX para ciertas acciones.

*   **`DevTools.template.php`**:
    *   **Propósito:** Contiene las funciones de presentación (HTML/PHP) que renderizan la interfaz de usuario dentro del tema de SMF.
    *   **Funcionalidad:** Define plantillas para las listas de paquetes, hooks y archivos, utilizando las funciones estándar de SMF (`template_show_list`).

### Sub-controladores (Directorio `DevTools/`)

*   **`DevTools/DevTools-Packages.php`**:
    *   **Clase:** `DevToolsPackages`
    *   **Propósito:** Gestiona la lógica de la sección "Paquetes".
    *   **Funcionalidad:**
        *   `listGetPackages()`: Obtiene la lista de paquetes instalados filtrando los comprimidos.
        *   `HooksReinstall()` / `HooksUninstall()`: Lee el `package-info.xml`, parsea las etiquetas `<hook>` y llama a funciones de SMF (`add_integration_function`, `remove_integration_function`) para actualizar la base de datos.
        *   `FilesSyncIn()` / `FilesSyncOut()`: Gestiona la copia de archivos entre directorios utilizando `copytree` y `package_put_contents`.

*   **`DevTools/DevTools-Hooks.php`**:
    *   **Clase:** `DevToolsHooks`
    *   **Propósito:** Gestiona la lógica de la sección "Hooks".
    *   **Funcionalidad:**
        *   `listGetHooks()`: Obtiene y filtra los hooks registrados en `$modSettings`.
        *   `toggleHook()`, `addHook()`, `deleteHook()`, `modifyHook()`: Realizan operaciones CRUD sobre los hooks en la base de datos.

*   **`DevTools/DevTools-Files.php`**:
    *   **Clase:** `DevToolsFiles`
    *   **Propósito:** Gestiona la creación de archivos comprimidos.
    *   **Funcionalidad:**
        *   `downloadArchive()`: Inicia el proceso de creación del archivo. Lee las exclusiones y metadatos del `package-info.xml` e instancia la clase de generación adecuada (`DevToolsFilePharZip` o `DevToolsFilePharTgz`).

### Generación de Archivos (Directorio `DevTools/`)

*   **`DevTools/DevTools-File-Base.php`**:
    *   **Clase:** `DevToolsFileBase` (Abstracta)
    *   **Propósito:** Clase base para la generación de archivos.
    *   **Funcionalidad:** Maneja la creación de directorios temporales, exclusiones de archivos y la descarga final del archivo generado al navegador (headers HTTP).
*   **`DevTools/DevTools-File-PharBase.php`**: Clase base para implementaciones usando Phar.
*   **`DevTools/DevTools-File-PharZip.php`**: Implementación concreta para crear `.zip`.
*   **`DevTools/DevTools-File-PharTgz.php`**: Implementación concreta para crear `.tgz` (tar.gz).

## 3. Análisis de Variables Clave

Las clases hacen un uso extensivo de la inyección de dependencias de las variables globales de SMF.

*   **`$context`**: Variable global fundamental en SMF.
    *   `$context['instances']`: Almacena las instancias de las clases del mod (`DevTools`, `DevToolsPackages`, etc.) para permitir el acceso cruzado.
    *   `$context['sub_template']`: Define qué función del archivo `DevTools.template.php` se debe renderizar.
    *   `$context['devtools_buttons']`: Define los botones de navegación (Paquetes, Hooks, Archivos) que se muestran en la interfaz.

*   **`$modSettings`**: Contiene la configuración global del foro y, crucialmente para este mod, la lista de hooks de integración activos (claves que empiezan por `integrate_`).
    *   `$modSettings['package_disable_cache']`: Se establece a `true` en operaciones de archivos para evitar que SMF use versiones cacheadas de la información de paquetes.

*   **`$smcFunc`**: Proporciona funciones de base de datos y utilidades seguras.
*   **`$packagesdir`**: Ruta absoluta al directorio `Packages/`.
*   **`$sourcedir`**: Ruta absoluta al directorio `Sources/`.

## 4. Flujo de Datos

1.  **Entrada:** El usuario accede a `index.php?action=devtools`.
2.  **Enrutamiento:** `DevTools::main_action` determina el área (`packages`, `hooks`, `files`) basándose en `$_REQUEST['area']`.
3.  **Procesamiento:** Se instancia la clase correspondiente (ej. `DevToolsPackages`). Esta clase interactúa con el sistema de archivos (para leer `package-info.xml`) o con la base de datos (para leer hooks).
4.  **Salida:** Los datos se preparan en `$context` y se carga la plantilla `DevTools.template.php` para mostrar la información al usuario.

## 5. Flujo de Trabajo Esperado (Workflow)

El flujo de trabajo típico para un desarrollador que utiliza estas herramientas es el siguiente:

1.  **Inicialización:**
    *   El desarrollador crea una carpeta para su mod dentro del directorio `Packages/` (por ejemplo, `Packages/mi-mod/`).
    *   Dentro de esta carpeta, coloca el archivo `package-info.xml` y la estructura de archivos del mod.

2.  **Desarrollo y Sincronización (Iterativo):**
    *   **Sync In:** Desde el panel de DevTools (`index.php?action=devtools;area=packages`), se usa la opción **"Sync In"** para copiar los archivos desde la carpeta del paquete hacia los directorios de la instalación activa de SMF (`Sources/`, `Themes/`, etc.). Esto permite probar los archivos en el entorno real.
    *   **Gestión de Hooks:** Si se edita el `package-info.xml` para añadir o modificar hooks, se utiliza la opción **"Reinstall Hooks"**. Esto aplica los cambios en la base de datos sin necesidad de desinstalar y reinstalar todo el paquete manualmente.
    *   **Sync Out (Opcional):** Si el desarrollador realiza cambios o correcciones directamente en los archivos de la instalación de SMF (por ejemplo, mientras depura un error en `Sources/Subs.php`), utiliza **"Sync Out"** para copiar esos cambios de vuelta a su carpeta de paquete (`Packages/mi-mod/`). Esto asegura que el código fuente del mod se mantenga actualizado con las correcciones realizadas en vivo.

3.  **Empaquetado y Distribución:**
    *   Una vez que el desarrollo es estable, el desarrollador navega a la pestaña **"Files"**.
    *   Selecciona el formato deseado (`zip` o `tgz`) para generar el archivo comprimido.
    *   La herramienta genera el paquete listo para distribuir, excluyendo automáticamente archivos de desarrollo (como `.git`, `.github`) definidos en la sección `<devtools><exclusion>` del `package-info.xml`.
