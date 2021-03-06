/*
 * deposit_section.h
 *
 *  Created on: 27/08/2010
 *      Author: pc
 */

#ifndef DEPOSIT_SECTION_H_
#define DEPOSIT_SECTION_H_

#include "document_section.h"

#include "../plugins/line_edit_plugin.h"
#include "../plugins/combo_box.h"

class DepositSection: public DocumentSection
{
	Q_OBJECT

public:
	DepositSection(QNetworkCookieJar *jar, QWebPluginFactory *factory,
			QUrl *serverUrl, QString cashRegisterKey, QWidget *parent = 0);
	virtual ~DepositSection() {};

public slots:
	void setNumber(QString number);
	void numberSetted(QString content);
	void setBankAccount(int index);
	void bankAccountSetted(QString content);
	void addCashDeposit();
	void saveDeposit();
	void searchDeposit();
	void showAuthenticationDialogForCancel();
	void cancelDocument();

protected:
	// Edit actions.
	QAction *m_AddItemAction;

	void setActions();
	void setMenu();
	void setActionsManager();
	void setPlugins();
	void updateActions();
	void prepareDocumentForm(QString username);

	void createDocumentEvent(bool ok, QList<QMap<QString, QString>*> *list = 0);

private:
	LineEditPlugin *m_SlipNumberLineEdit;
	ComboBox *m_BankAccountComboBox;

	QString navigateValues();
};

#endif /* DEPOSIT_SECTION_H_ */
