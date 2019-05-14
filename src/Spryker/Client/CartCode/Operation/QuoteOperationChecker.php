<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\CartCode\Operation;

use Generated\Shared\Transfer\CartCodeOperationMessageTransfer;
use Generated\Shared\Transfer\CartCodeOperationResultTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\CartCode\Dependency\Client\CartCodeToQuoteClientInterface;

class QuoteOperationChecker implements QuoteOperationCheckerInterface
{
    /**
     * @var \Spryker\Client\CartCode\Dependency\Client\CartCodeToQuoteClientInterface
     */
    protected $quoteClient;

    protected const GLOSSARY_KEY_LOCKED_CART_CHANGE_DENIED = 'cart.locked.change_denied';

    /**
     * @param \Spryker\Client\CartCode\Dependency\Client\CartCodeToQuoteClientInterface $quoteClient
     */
    public function __construct(CartCodeToQuoteClientInterface $quoteClient)
    {
        $this->quoteClient = $quoteClient;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CartCodeOperationResultTransfer|null
     */
    public function checkLockedQuoteResponse(QuoteTransfer $quoteTransfer): ?CartCodeOperationResultTransfer
    {
        if (!$this->quoteClient->isQuoteLocked($quoteTransfer)) {
            return null;
        }

        $cartCodeCalculationMessageTransfer = new CartCodeOperationMessageTransfer();
        $cartCodeCalculationMessageTransfer->setIsSuccess(false);

        $messageTransfer = new MessageTransfer();
        $messageTransfer->setValue(static::GLOSSARY_KEY_LOCKED_CART_CHANGE_DENIED);
        $cartCodeCalculationMessageTransfer->setMessage($messageTransfer);

        $cartCodeOperationResultTransfer = new CartCodeOperationResultTransfer();
        $cartCodeOperationResultTransfer
            ->setQuote($quoteTransfer)
            ->addMessage($cartCodeCalculationMessageTransfer);

        return $cartCodeOperationResultTransfer;
    }
}
