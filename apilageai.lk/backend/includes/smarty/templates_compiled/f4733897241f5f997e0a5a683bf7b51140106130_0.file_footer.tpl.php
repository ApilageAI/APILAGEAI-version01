<?php
/* Smarty version 5.7.0, created on 2026-02-04 22:15:10
  from 'file:components/footer.tpl' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.7.0',
  'unifunc' => 'content_6983779614e6b7_46543571',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f4733897241f5f997e0a5a683bf7b51140106130' => 
    array (
      0 => 'components/footer.tpl',
      1 => 1770222636,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_6983779614e6b7_46543571 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/Users/dinethgunawardana/Downloads/apilageai/backend/includes/smarty/templates/components';
?>  <!-- Footer -->
     <footer class="bg-white text-brand-dark pt-20 pb-10 border-t-2 border-brand-dark">
        <div class="container mx-auto px-6">
          <div class="grid md:grid-cols-4 gap-12 mb-16">
            <div class="col-span-2 md:col-span-1">
              <div class="flex items-center gap-3 mb-6">
                <img
                  src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/images/icon.png"
                  alt="ApilageAI Logo"
                  class="w-10 h-10 object-contain"
                />
                <span class="text-2xl font-bold font-display">
                  ApilageAI
                </span>
              </div>
              <p class="text-brand-dark/70 text-sm leading-relaxed max-w-xs font-medium">
                Sri Lanka's first native AI platform. Empowering the next generation of learners with smart, accessible technology.
              </p>
              <div class="mt-4 text-sm font-medium text-brand-dark/70">
                <p>Apilageai PVT LTD</p>
                <p>Kalutara South Sri Lanka</p>
                <p>Contact: +94701840527</p>
              </div>
            </div>
            
            <div>
              <h4 class="font-bold text-brand-dark mb-6 uppercase tracking-wider font-display border-b-2 border-brand-dark inline-block">Product</h4>
              <ul class="space-y-4 text-sm font-bold text-brand-dark/60">
                <li class="hover:text-brand-red transition-colors cursor-pointer hover:translate-x-1 duration-200"><a href="#pricing">Pricing</a></li>
                <li class="hover:text-brand-red transition-colors cursor-pointer hover:translate-x-1 duration-200"><a href="https://api.apilageai.lk">API Docs</a></li>
                <li class="hover:text-brand-red transition-colors cursor-pointer hover:translate-x-1 duration-200"><a href="#mobile">Mobile App</a></li>
              </ul>
            </div>

            <div>
              <h4 class="font-bold text-brand-dark mb-6 uppercase tracking-wider font-display border-b-2 border-brand-dark inline-block">Legal</h4>
              <ul class="space-y-4 text-sm font-bold text-brand-dark/60">
                <li class="hover:text-brand-red transition-colors cursor-pointer hover:translate-x-1 duration-200"><a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/privacypolicy/">Privacy Policy</a></li>
                <li class="hover:text-brand-red transition-colors cursor-pointer hover:translate-x-1 duration-200"><a href="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/termsconditions/">Terms of Service</a></li>
              </ul>
            </div>

            <div>
              <h4 class="font-bold text-brand-dark mb-6 uppercase tracking-wider font-display border-b-2 border-brand-dark inline-block">Safe Payment</h4>
              <div class="flex gap-4 mb-4">
                 <div class="w-14 h-10 border-2 border-brand-dark rounded bg-white flex items-center justify-center">
                    <svg class="w-12 h-8" viewBox="0 0 36 24" xmlns="http://www.w3.org/2000/svg">
                       <rect width="36" height="24" rx="2" fill="white" stroke="#172554" stroke-width="1"/>
                       <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="10" font-weight="bold" fill="#1434CB">VISA</text>
                    </svg>
                 </div>
                 <div class="w-14 h-10 border-2 border-brand-dark rounded bg-white flex items-center justify-center">
                    <svg class="w-12 h-8" viewBox="0 0 36 24" xmlns="http://www.w3.org/2000/svg">
                       <rect width="36" height="24" rx="2" fill="white" stroke="#172554" stroke-width="1"/>
                       <circle cx="14" cy="12" r="6" fill="#EB001B" opacity="0.8"/>
                       <circle cx="22" cy="12" r="6" fill="#F79E1B" opacity="0.8"/>
                    </svg>
                 </div>
              </div>
              <p class="text-xs text-brand-dark/50 font-medium">Secured by SSL. We respect your privacy.</p>
            </div>
          </div>
          
          <div class="border-t-2 border-brand-dark/10 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-brand-dark/50 font-bold">© 2025 Apilage AI. Made in Sri Lanka 🇱🇰.</p>
            <div class="flex gap-4">
              <div class="w-8 h-8 border-2 border-brand-dark rounded flex items-center justify-center hover:bg-brand-red hover:text-white transition-colors cursor-pointer shadow-hard-sm">
                <span class="font-bold text-xs">Fb</span>
              </div>
              <div class="w-8 h-8 border-2 border-brand-dark rounded flex items-center justify-center hover:bg-brand-red hover:text-white transition-colors cursor-pointer shadow-hard-sm">
                <span class="font-bold text-xs">Ig</span>
              </div>
               <div class="w-8 h-8 border-2 border-brand-dark rounded flex items-center justify-center hover:bg-brand-red hover:text-white transition-colors cursor-pointer shadow-hard-sm">
                <span class="font-bold text-xs">Lk</span>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>

<?php echo '<script'; ?>
 src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo (defined('APP_URL') ? constant('APP_URL') : null);?>
/assets/scripts/main.js?V=<?php echo $_smarty_tpl->getSmarty()->getModifierCallback('get_hash_token')();?>
"><?php echo '</script'; ?>
>

</body>
</html>
<?php }
}
