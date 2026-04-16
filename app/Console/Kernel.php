protected function schedule(Schedule $schedule)
{
    $schedule->command('documentos:verificar-vencimientos')
        ->dailyAt('08:00'); // puedes cambiar hora
}