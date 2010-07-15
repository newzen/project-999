/*
 * cash_receipt_section.h
 *
 *  Created on: 15/06/2010
 *      Author: pc
 */

#ifndef CASH_RECEIPT_SECTION_H_
#define CASH_RECEIPT_SECTION_H_

#include "section.h"

#include <QMainWindow>
#include <QTimer>
#include <QQueue>
#include <QXmlQuery>
#include "../console/console.h"
#include "../http_request/http_request.h"
#include "../xml_response_handler/xml_response_handler.h"

class CashReceiptSection: public Section
{
	Q_OBJECT

public:
	CashReceiptSection(QNetworkCookieJar *jar, QWebPluginFactory *factory,
			QUrl *serverUrl, QString cashReceiptKey, QWidget *parent = 0);
	virtual ~CashReceiptSection();
	void loadUrl();

public slots:
	void loadFinished(bool ok);
	void setCash();
	void addTimerObject();
	void checkForChanges();
	void updateChangeValue(QString content);
	void showVoucherDialog();

private:
	QString m_CashReceiptKey;
	Console *m_Console;
	QMainWindow *m_Window;
	QTimer m_CheckerTimer;
	QTimer m_SenderTimer;
	QString m_CashValue;
	QQueue<QString> m_CashValues;
	HttpRequest *m_CashRequest;
	XmlResponseHandler *m_Handler;
	QXmlQuery *m_Query;
	QString m_StyleSheet;
	HttpRequest *m_Request;

	// File actions.
	QAction *m_SaveAction;
	QAction *m_ExitAction;

	// Edit actions.
	QAction *m_AddVoucherAction;
	QAction *m_DeleteVoucherAction;

	// View actions.
	QAction *m_ScrollUpAction;
	QAction *m_ScrollDownAction;

	void setActions();
	void setMenu();
	void fetchStyleSheet();
	void fetchVouchers();
};

#endif /* CASH_RECEIPT_SECTION_H_ */