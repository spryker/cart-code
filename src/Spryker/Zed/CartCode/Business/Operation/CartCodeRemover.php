<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CartCode\Business\Operation;

use Generated\Shared\Transfer\CartCodeRequestTransfer;
use Generated\Shared\Transfer\CartCodeResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\CartCode\Dependency\Facade\CartCodeToCalculationFacadeInterface;

class CartCodeRemover implements CartCodeRemoverInterface
{
    /**
     * @var \Spryker\Zed\CartCode\Dependency\Facade\CartCodeToCalculationFacadeInterface
     */
    protected $calculationFacade;

    /**
     * @var \Spryker\Zed\CartCode\Business\Operation\QuoteOperationCheckerInterface
     */
    protected $quoteOperationChecker;

    /**
     * @var \Spryker\Zed\CartCodeExtension\Dependency\Plugin\CartCodePluginInterface[]
     */
    protected $cartCodePlugins;

    /**
     * @param \Spryker\Zed\CartCode\Dependency\Facade\CartCodeToCalculationFacadeInterface $calculationClient
     * @param \Spryker\Zed\CartCode\Business\Operation\QuoteOperationCheckerInterface $quoteOperationChecker
     * @param \Spryker\Zed\CartCodeExtension\Dependency\Plugin\CartCodePluginInterface[] $cartCodePlugins
     */
    public function __construct(
        CartCodeToCalculationFacadeInterface $calculationClient,
        QuoteOperationCheckerInterface $quoteOperationChecker,
        array $cartCodePlugins = []
    ) {
        $this->calculationFacade = $calculationClient;
        $this->quoteOperationChecker = $quoteOperationChecker;
        $this->cartCodePlugins = $cartCodePlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\CartCodeRequestTransfer $cartCodeRequestTransfer
     *
     * @return \Generated\Shared\Transfer\CartCodeResponseTransfer
     */
    public function removeCartCode(CartCodeRequestTransfer $cartCodeRequestTransfer): CartCodeResponseTransfer
    {
        $quoteTransfer = $cartCodeRequestTransfer->getQuote();
        $lockedCartCodeOperationResultTransfer = $this->quoteOperationChecker->checkLockedQuoteResponse($quoteTransfer);
        if ($lockedCartCodeOperationResultTransfer) {
            return $lockedCartCodeOperationResultTransfer;
        }

        $quoteTransfer = $this->executeCartCodePlugins($cartCodeRequestTransfer);
        $quoteTransfer = $this->calculationFacade->recalculateQuote($quoteTransfer);

        return (new CartCodeResponseTransfer())->setQuote($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CartCodeRequestTransfer $cartCodeRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function executeCartCodePlugins(CartCodeRequestTransfer $cartCodeRequestTransfer): QuoteTransfer
    {
        $quoteTransfer = $cartCodeRequestTransfer->getQuote();
        foreach ($this->cartCodePlugins as $cartCodePlugin) {
            $quoteTransfer = $cartCodePlugin->removeCartCode($cartCodeRequestTransfer)->getQuote();
        }

        return $quoteTransfer;
    }
}
