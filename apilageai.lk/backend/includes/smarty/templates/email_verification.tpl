{include file="components/head.tpl"}

<div class="auth-layout auth-layout--verify">
    <div class="background-accent top-right"></div>
    <div class="background-accent bottom-left"></div>
    <div class="bg-pattern"></div>

    <div class="auth-wrapper">
        <div class="auth-container" style="max-width: 600px; margin: 0 auto;">
            <div class="verification-result-card animate-fade-in">
                {if $result.e}
                    {* Verification Failed *}
                    <div class="verification-failed">
                        <i class="fa-solid fa-times-circle"></i>
                        <h2>Verification Failed</h2>
                        <p>{$result.m}</p>
                        
                        {if isset($result.expired) && $result.expired}
                            <button onclick="requestNewLink()" class="auth-submit-button" style="margin-top: 20px;">
                                <i class="fa-solid fa-envelope"></i> Request New Verification Link
                            </button>
                        {/if}
                        
                        <a href="{$smarty.const.APP_URL}/auth/login" class="back-link">
                            <i class="fa-solid fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                {else}
                    {* Verification Success *}
                    <div class="verification-success">
                        <div class="success-animation">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <h2>Email Verified!</h2>
                        <p>{$result.m}</p>
                        
                        {if isset($result.already_verified) && $result.already_verified}
                            <p class="info-text">Your email was already verified.</p>
                        {else}
                            <p class="info-text">Welcome to Apilage AI! You can now access all features.</p>
                        {/if}
                        
                        <a href="{$smarty.const.APP_URL}/app" class="auth-submit-button" style="margin-top: 20px;">
                            Go to App <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>


<script>
function requestNewLink() {
    const email = prompt('Enter your email address to receive a new verification link:');
    
    if (!email) {
        return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }

    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

    fetch('{$smarty.const.APP_URL}/auth/resend-verification', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded' 
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.e) {
            alert('Error: ' + data.m);
        } else {
            alert('Success! ' + data.m + '\n\nPlease check your email inbox.');
            setTimeout(() => {
                window.location.href = '{$smarty.const.APP_URL}/auth/login';
            }, 2000);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error('Error:', err);
        alert('Failed to send verification email. Please try again or contact support.');
    });
}

// Auto-redirect after successful verification
{if !$result.e && !isset($result.already_verified)}
    setTimeout(function() {
        window.location.href = '{$smarty.const.APP_URL}/app';
    }, 5000);
{/if}
</script>

</body>
</html>
