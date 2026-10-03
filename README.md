# ClipDarija

ClipDarija is a local-first highlight finder concept made for Moroccan streamers. The current MVP provides a polished workspace for selecting a recording, choosing highlight signals, configuring clip duration and count, and reviewing a generated shortlist.

## Run locally

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
php artisan serve
```

Visit `http://127.0.0.1:8000`.

## Fastest deployment: Vercel (no Git required)

The included `vercel.json` tells Vercel to run `npm run build:static` and publish
the generated `dist/` folder. On Vercel's free Hobby plan this static prototype
does not need a server or database.

1. Install [Node.js](https://nodejs.org/) if `node --version` does not work.
2. Open Terminal (macOS/Linux) or PowerShell (Windows) in this project folder.
3. Run:

   ```bash
   npm install
   npx vercel
   ```

4. A browser opens so you can create or sign in to a Vercel account. Back in the
   terminal, accept the defaults. When asked whether to modify project settings,
   answer **No** because `vercel.json` already has the correct settings.
5. Vercel prints a preview URL. Make it the permanent production website with:

   ```bash
   npx vercel --prod
   ```

For later updates, return to the project folder and run `npx vercel --prod`
again. This method uploads the project directly and does not require GitHub.

## Vercel with GitHub automatic updates

Use this method if you want every future push to deploy automatically.

### 1. Create an empty GitHub repository

1. Sign in at [github.com](https://github.com/).
2. Click **+ → New repository**.
3. Name it `clipdarija`, choose **Public**, and do **not** add a README,
   `.gitignore`, or license.
4. Click **Create repository**, then copy the HTTPS URL shown by GitHub. It will
   look like `https://github.com/YOUR-USERNAME/clipdarija.git`.

### 2. Push this project

In a terminal opened inside this project, run the commands below. Replace the
example URL with the URL copied from GitHub:

```bash
git status
git remote add origin https://github.com/YOUR-USERNAME/clipdarija.git
git push -u origin HEAD:main
```

If `git remote add origin` says that `origin` already exists, use this instead:

```bash
git remote set-url origin https://github.com/YOUR-USERNAME/clipdarija.git
git push -u origin HEAD:main
```

GitHub may open a browser login window. Complete it; GitHub account passwords
are not accepted directly in the terminal. Refresh the repository page after the
push and the files should appear.

### 3. Import it into Vercel

1. Sign in at [vercel.com](https://vercel.com/) using GitHub.
2. Click **Add New → Project**, find `clipdarija`, and click **Import**.
3. Leave **Framework Preset** as **Other**. Vercel reads these values from
   `vercel.json`:
   - Build command: `npm run build:static`
   - Output directory: `dist`
4. No environment variables are needed. Click **Deploy**.

Vercel displays the live `.vercel.app` address when the build finishes. Future
pushes to `main` deploy automatically:

```bash
git add .
git commit -m "Describe your update"
git push
```

## Deploy free with GitHub Pages

ClipDarija's current workspace runs entirely in the browser, so it can be hosted
on GitHub Pages for free without a card, server, database, or sleeping service.
The included GitHub Actions workflow builds and publishes it automatically.

1. Push this branch to a GitHub repository.
2. Open **Settings → Pages** in that repository.
3. Under **Build and deployment → Source**, choose **GitHub Actions**.
4. Open **Actions**, select **Deploy ClipDarija to GitHub Pages**, and click
   **Run workflow**. A push to `work`, `main`, or `master` also triggers it.

The deployment job displays the public URL when it finishes. For a repository
named `clipdarija`, it normally looks like
`https://YOUR-USERNAME.github.io/clipdarija/`. Subsequent pushes update the site
automatically.

To preview the exact static deployment locally:

```bash
npm ci
npm run build:static
npx serve dist
```

The generated `dist/` directory has no PHP dependency and can also be dragged
onto any static host.

### Optional: run the full Laravel image locally

```bash
docker build -t clipdarija .
docker run --rm -p 8080:80 \
  -e APP_KEY="$(php artisan key:generate --show)" \
  clipdarija
```

Visit `http://127.0.0.1:8080`; the container health endpoint is available at
`http://127.0.0.1:8080/up`.

## Current prototype flow

1. Choose or drag an MP4, MOV, or MKV recording into the workspace.
2. Select the moments to prioritize: reactions, chess events, or chat moments.
3. Configure clip length, number of candidates, and Darija captions.
4. Run the interactive analysis demo and review candidate clips.

The interface is intentionally local-first. Actual Whisper transcription, audio analysis, FFmpeg cutting, and export processing are the next backend integration milestone; the current analysis and export actions demonstrate the complete product flow without sending video anywhere.

## Development

```bash
npm run dev
php artisan test
```
