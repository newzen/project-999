/*
 * sales_report_section.cpp
 *
 *  Created on: 20/09/2010
 *      Author: pc
 */

#include "sales_report_section.h"

#include <QMenuBar>

/**
 * @class SalesReportSection
 * Section for displaying the sales report.
 */

/**
 * Constructs the section.
 */
SalesReportSection::SalesReportSection(QNetworkCookieJar *jar,
		QWebPluginFactory *factory, QUrl *serverUrl, QString cashRegisterKey,
		QWidget *parent) : Section(jar, factory, serverUrl, parent),
		m_CashRegisterKey(cashRegisterKey)
{
	m_Window = dynamic_cast<QMainWindow*>(parentWidget());
	setActions();
	setMenu();

	connect(ui.webView, SIGNAL(loadFinished(bool)), this, SLOT(loadFinished(bool)));
}

/**
 * Creates the QActions for the menu bar.
 */
void SalesReportSection::setActions()
{
	m_PrintAction = new QAction("Imprimir", this);
	m_PrintAction->setShortcut(tr("Ctrl+P"));

	m_ExitAction = new QAction("Salir", this);
	m_ExitAction->setShortcut(Qt::Key_Escape);
	connect(m_ExitAction, SIGNAL(triggered()), m_Window, SLOT(close()));
}

/**
 * Sets the window's menu bar.
 */
void SalesReportSection::setMenu()
{
	QMenu *menu;

	menu = m_Window->menuBar()->addMenu("Archivo");
	menu->addAction(m_PrintAction);
	menu->addSeparator();
	menu->addAction(m_ExitAction);
}
