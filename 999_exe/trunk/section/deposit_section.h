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

protected:
	void setActions();
	void setMenu();
	void setActionsManager();
	void installRecordsetSearcher();
	void setPlugins();
	void updateActions();
	void prepareDocumentForm(QString dateTime, QString username);

	void createDocumentEvent(bool ok, QList<QMap<QString, QString>*> *list = 0);

private:
	LineEditPlugin *m_DepositNumberLineEdit;
	ComboBox *m_BankAccountComboBox;

	QString navigateValues();
};

#endif /* DEPOSIT_SECTION_H_ */
