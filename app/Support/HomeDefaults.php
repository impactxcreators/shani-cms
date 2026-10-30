<?php

namespace App\Support;

/** Starter text for home blocks, shown until the admin fills in their own. Only claims the site can back up. */
class HomeDefaults
{
    public const WHY_US = [
        ['icon' => '🏷️', 'title' => 'Every offer in one place', 'text' => 'Coupon codes and deals from hundreds of stores, sorted so the best ones are on top.'],
        ['icon' => '✅', 'title' => 'Verified badges you can trust', 'text' => 'The Verified badge only appears on codes our team has checked.'],
        ['icon' => '⏱️', 'title' => 'Expired offers removed', 'text' => 'Offers are taken down automatically once they expire, so you are not chasing dead codes.'],
        ['icon' => '📋', 'title' => 'One-tap copy', 'text' => 'Tap Show code and the code is copied for you, ready to paste at checkout.'],
        ['icon' => '💸', 'title' => 'Always free', 'text' => 'No sign-up and no fees. Just find a code and save.'],
        ['icon' => '🔔', 'title' => 'Deal alerts', 'text' => 'Turn on alerts to hear about the best new deals, a few times a week at most.'],
    ];

    public const FAQ = [
        ['question' => 'What is a coupon code?', 'answer' => 'A coupon code is a short word or set of letters and numbers that a store accepts at checkout for a discount, free shipping or another perk.'],
        ['question' => 'How do I use a coupon code?', 'answer' => 'Tap Show code to reveal and copy it. The store opens in a new tab. Add your items to the cart, paste the code in the promo or discount box at checkout and apply it.'],
        ['question' => 'What is the difference between a code and a deal?', 'answer' => 'A code has to be entered at checkout. A deal needs no code: the discount is already applied when you open the store through the Show deal button.'],
        ['question' => 'Can I use more than one code at a time?', 'answer' => 'Most stores allow one code per order, but some let you combine a code with a sale price. The store\'s checkout will tell you if a code cannot be combined.'],
        ['question' => 'Is it free to use?', 'answer' => 'Yes. Using the site is completely free. We may earn a small commission from the store when you shop through our links, at no extra cost to you.'],
    ];
}
