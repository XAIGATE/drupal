<?php

namespace Drupal\commerce_xaigate\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the Off-site Redirect payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "xaigate_offsite_redirect",
 *   label = "Xaigate (Off-site redirect)",
 *   display_label = "Xaigate",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_xaigate\PluginForm\OffsiteRedirect\XaigateOffsiteForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard", "visa",
 *   },
 * )
 */
class XaigateOffsiteRedirect extends OffsitePaymentGatewayBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'apikey' => '',
      'shopname' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['apikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#default_value' => $this->configuration['apikey'],
      '#required' => TRUE,
      '#description' => $this->t('You can get the @key in your account.',
        ['@key => <a href="https://wallet.xaigate.com/merchant/credential">API key</a>']),
    ];

    $form['shopname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shop name'),
      '#default_value' => $this->configuration['shopname'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['apikey'] = $values['apikey'];
      $this->configuration['shopname'] = $values['shopname'];
      
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'state' => 'draft',
      'amount' => $order->getBalance(),
      'payment_gateway' => $this->parentEntity->id(),
      'order_id' => $order->id(),
      'remote_id' => $request->request->get('invoice_id'),
    ]);
    $payment->save();
  }

}
