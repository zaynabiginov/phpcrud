<nav class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-200">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-slate-900 dark:text-white">StockFlow</span>
            </div>

            <div class="hidden md:flex items-center gap-7 text-sm font-medium">
                <a href="index.php" class="text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors px-3 py-2 rounded-lg <?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : '' ?>">Панель</a>
                <a href="items.php" class="text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors px-3 py-2 rounded-lg <?= basename($_SERVER['SCRIPT_NAME']) === 'items.php' ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : '' ?>">Товары</a>
                <a href="movements.php" class="text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors px-3 py-2 rounded-lg <?= basename($_SERVER['SCRIPT_NAME']) === 'movements.php' ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : '' ?>">Движения</a>
            </div>

            <div class="flex items-center gap-3">
                <button id="themeToggle" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" aria-label="Сменить тему">
                    <i class="fa-solid fa-moon text-lg"></i>
                </button>
                <a href="item-create.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 transition-all transform active:scale-95">
                    <i class="fa-solid fa-plus"></i>
                    Новый товар
                </a>
            </div>
        </div>
    </div>
</nav>
