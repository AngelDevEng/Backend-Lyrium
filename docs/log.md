2026-04-01T19:31:12.465872142Z [inf]  Starting Container
2026-04-01T19:31:15.323719771Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:15.323727615Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:15.323735438Z [inf]  
2026-04-01T19:31:15.323741790Z [inf]     Error 
2026-04-01T19:31:15.323747988Z [inf]  
2026-04-01T19:31:15.323754556Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:15.323760360Z [inf]  
2026-04-01T19:31:15.323766253Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:15.323772986Z [inf]      169▕      */
2026-04-01T19:31:15.323778742Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:15.323784461Z [inf]      171▕     {
2026-04-01T19:31:15.323789848Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:15.323798899Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:15.323806849Z [inf]      174▕         }
2026-04-01T19:31:15.323814645Z [inf]      175▕ 
2026-04-01T19:31:15.323820937Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:15.323827687Z [inf]      177▕
2026-04-01T19:31:15.323835549Z [inf]  
2026-04-01T19:31:15.323841900Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:15.323848036Z [inf]  
2026-04-01T19:31:15.323854386Z [inf]    6   artisan:16
2026-04-01T19:31:15.323861281Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:15.323866917Z [inf]  
2026-04-01T19:31:16.990268130Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:16.990273752Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:16.990282424Z [inf]  
2026-04-01T19:31:16.990289407Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:16.990297431Z [inf]      169▕      */
2026-04-01T19:31:16.990298346Z [inf]  
2026-04-01T19:31:16.990304776Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:16.990311479Z [inf]      171▕     {
2026-04-01T19:31:16.990321044Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:16.990329669Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:16.990337564Z [inf]      174▕         }
2026-04-01T19:31:16.990350348Z [inf]      175▕ 
2026-04-01T19:31:16.990357825Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:16.990364265Z [inf]      177▕
2026-04-01T19:31:16.990390945Z [inf]  
2026-04-01T19:31:16.990398459Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:16.990404937Z [inf]  
2026-04-01T19:31:16.990410635Z [inf]    6   artisan:16
2026-04-01T19:31:16.990431822Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:16.990438249Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:16.990456546Z [inf]  
2026-04-01T19:31:16.990464523Z [inf]     Error 
2026-04-01T19:31:16.990471437Z [inf]  
2026-04-01T19:31:18.660212894Z [inf]      175▕ 
2026-04-01T19:31:18.660222293Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:18.660232231Z [inf]      177▕
2026-04-01T19:31:18.660232497Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:18.660243181Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:18.660250287Z [inf]  
2026-04-01T19:31:18.660257390Z [inf]     Error 
2026-04-01T19:31:18.660263817Z [inf]  
2026-04-01T19:31:18.660321882Z [inf]  
2026-04-01T19:31:18.660334295Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:18.660348558Z [inf]  
2026-04-01T19:31:18.660356716Z [inf]    6   artisan:16
2026-04-01T19:31:18.660366369Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:18.660374871Z [inf]  
2026-04-01T19:31:18.660413839Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:18.660425716Z [inf]  
2026-04-01T19:31:18.660433955Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:18.660442246Z [inf]      169▕      */
2026-04-01T19:31:18.660464103Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:18.660471065Z [inf]      171▕     {
2026-04-01T19:31:18.660477479Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:18.660483987Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:18.660492950Z [inf]      174▕         }
2026-04-01T19:31:20.402268465Z [inf]      171▕     {
2026-04-01T19:31:20.402274496Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:20.402277438Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:20.402279995Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:20.402287297Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:20.402288116Z [inf]      169▕      */
2026-04-01T19:31:20.402291139Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:20.402297625Z [inf]  
2026-04-01T19:31:20.402298558Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:20.402300294Z [inf]      174▕         }
2026-04-01T19:31:20.402306448Z [inf]     Error 
2026-04-01T19:31:20.402314049Z [inf]  
2026-04-01T19:31:20.402320935Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:20.402327740Z [inf]  
2026-04-01T19:31:20.402725478Z [inf]      175▕ 
2026-04-01T19:31:20.402734877Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:20.402741851Z [inf]      177▕
2026-04-01T19:31:20.402748268Z [inf]  
2026-04-01T19:31:20.402753994Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:20.402761204Z [inf]  
2026-04-01T19:31:20.402767712Z [inf]    6   artisan:16
2026-04-01T19:31:20.402772990Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:20.402779074Z [inf]  
2026-04-01T19:31:21.984507685Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:21.984512677Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:21.984517952Z [inf]  
2026-04-01T19:31:21.984524716Z [inf]     Error 
2026-04-01T19:31:21.984529738Z [inf]  
2026-04-01T19:31:21.984548070Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:21.984554395Z [inf]  
2026-04-01T19:31:21.984560140Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:21.984565804Z [inf]      169▕      */
2026-04-01T19:31:21.984570977Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:21.984575931Z [inf]      171▕     {
2026-04-01T19:31:21.984582946Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:21.984589983Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:21.984624010Z [inf]      174▕         }
2026-04-01T19:31:21.984635977Z [inf]      175▕ 
2026-04-01T19:31:21.984641709Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:21.984652896Z [inf]      177▕
2026-04-01T19:31:21.984658995Z [inf]  
2026-04-01T19:31:21.984666637Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:21.984673547Z [inf]  
2026-04-01T19:31:21.984684495Z [inf]    6   artisan:16
2026-04-01T19:31:21.984694347Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:21.984702117Z [inf]  
2026-04-01T19:31:23.810735905Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:23.810746507Z [inf]      169▕      */
2026-04-01T19:31:23.810753656Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:23.810761833Z [inf]      171▕     {
2026-04-01T19:31:23.810768137Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:23.810774307Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:23.810780347Z [inf]      174▕         }
2026-04-01T19:31:23.810787637Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:23.810794066Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:23.810800924Z [inf]  
2026-04-01T19:31:23.810808465Z [inf]     Error 
2026-04-01T19:31:23.810816266Z [inf]  
2026-04-01T19:31:23.810823298Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:23.810830772Z [inf]  
2026-04-01T19:31:23.810864412Z [inf]  
2026-04-01T19:31:23.810867590Z [inf]      175▕ 
2026-04-01T19:31:23.810873596Z [inf]    6   artisan:16
2026-04-01T19:31:23.810877778Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:23.810881763Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:23.810888187Z [inf]      177▕
2026-04-01T19:31:23.810890979Z [inf]  
2026-04-01T19:31:23.810896214Z [inf]  
2026-04-01T19:31:23.810903170Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:25.413235672Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:25.413244168Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:25.413250732Z [inf]  
2026-04-01T19:31:25.413259490Z [inf]     Error 
2026-04-01T19:31:25.413268454Z [inf]  
2026-04-01T19:31:25.413276596Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:25.413283455Z [inf]  
2026-04-01T19:31:25.413288187Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:25.413294520Z [inf]      169▕      */
2026-04-01T19:31:25.413301779Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:25.413307239Z [inf]      171▕     {
2026-04-01T19:31:25.413314294Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:25.413319360Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:25.413372276Z [inf]      174▕         }
2026-04-01T19:31:25.413376656Z [inf]      175▕ 
2026-04-01T19:31:25.413383350Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:25.413387395Z [inf]      177▕
2026-04-01T19:31:25.413394282Z [inf]  
2026-04-01T19:31:25.413437351Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:25.413450021Z [inf]  
2026-04-01T19:31:25.413464424Z [inf]    6   artisan:16
2026-04-01T19:31:25.413470130Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:25.413479583Z [inf]  
2026-04-01T19:31:27.091264873Z [inf]  
2026-04-01T19:31:27.091277374Z [inf]  
2026-04-01T19:31:27.091278192Z [inf]        [2m+5 vendor frames [22m
2026-04-01T19:31:27.091285093Z [inf]    6   artisan:16
2026-04-01T19:31:27.091296046Z [inf]        Illuminate\Foundation\Application::handleCommand()
2026-04-01T19:31:27.091303914Z [inf]  
2026-04-01T19:31:27.091322300Z [inf]      170▕     public function getSubscribedSignals(): array
2026-04-01T19:31:27.091330778Z [inf]      171▕     {
2026-04-01T19:31:27.091334419Z [inf]    Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:27.091340488Z [inf]      172▕         if (! windows_os()) {
2026-04-01T19:31:27.091346546Z [inf]  
2026-04-01T19:31:27.091352956Z [inf]    ➜ 173▕             return [SIGINT, SIGTERM, SIGTSTP];
2026-04-01T19:31:27.091355889Z [inf]    at vendor/laravel/reverb/src/Servers/Reverb/Console/Commands/StartServer.php:173
2026-04-01T19:31:27.091362986Z [inf]      174▕         }
2026-04-01T19:31:27.091364416Z [inf]      169▕      */
2026-04-01T19:31:27.091374295Z [inf]      175▕ 
2026-04-01T19:31:27.091377057Z [inf]  ==> Starting Laravel Reverb on 0.0.0.0:8080
2026-04-01T19:31:27.091386394Z [err]  Undefined constant "Laravel\Reverb\Servers\Reverb\Console\Commands\SIGINT"
2026-04-01T19:31:27.091389015Z [inf]      176▕         $this->handleSignalWindows();
2026-04-01T19:31:27.091396469Z [inf]      177▕
2026-04-01T19:31:27.091400034Z [inf]  
2026-04-01T19:31:27.091412518Z [inf]     Error 
2026-04-01T19:31:27.091418776Z [inf]  