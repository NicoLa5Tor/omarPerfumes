# Perfumes Block

Bloque de WordPress construido con **React** (Gutenberg). Se desarrolla localmente y se despliega a tu servidor WordPress por **FTP** usando **GitHub Actions** — igual que Shopify CLI.

## Estructura

```
Perfumes/
├── perfumes-block.php     # Plugin de WordPress que registra el bloque
├── src/                   # Código React (se compila a JS)
│   ├── block.json         # Metadatos del bloque
│   ├── index.js           # Punto de entrada React
│   ├── edit.js            # Vista del editor (admin)
│   ├── save.js            # Salida guardada (frontend)
│   ├── editor.scss        # Estilos del editor
│   └── style.scss         # Estilos del frontend
├── .github/workflows/     # Deploy automático por FTP
└── package.json           # Dependencias y scripts
```

## 1. Desarrollo local

Requisitos: Node.js 20+ instalado.

```bash
npm install        # Instala dependencias (incluye wp-scripts)
npm run start      # Modo desarrollo (recarga en caliente)
npm run build      # Compila a /build para producción
```

Para verlo en WordPress, activa el plugin. (Para un entorno local completo con
WordPress + MySQL, se puede agregar Docker después.)

## 2. Deploy automático por FTP (GitHub Actions)

Cada vez que haces `push` a la rama `main`, GitHub compila React y sube los
archivos a tu servidor por FTP.

### Configurar los secrets en GitHub

En tu repositorio: **Settings → Secrets and variables → Actions → New repository secret**

| Nombre | Valor |
|---|---|
| `FTP_HOST` | Host de tu FTP (ej. `ftp.tudominio.com`) |
| `FTP_USER` | Usuario FTP |
| `FTP_PASS` | Contraseña FTP |

Los archivos se suben a: `/wp-content/plugins/perfumes-block/`

## 3. Flujo completo (tipo Shopify)

```
editas aquí (src/)  →  npm run build  →  git push  →  GitHub Actions
   →  FTP sube /build + plugin.php  →  WordPress en producción
```

Solo se sube lo compilado (`/build`) y `perfumes-block.php`. **Nunca** se suben
`node_modules`, `src/`, ni `package.json`.
