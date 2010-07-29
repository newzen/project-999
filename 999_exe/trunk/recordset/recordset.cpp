#include "recordset.h"

/**
 * @class Recordset
 * Manages a recordset with the list with the ids of the document in use. It also
 * displays the position in which the recordset is at.
 */

/**
 * Constructs a Recordset.
 */
Recordset::Recordset(QWidget *parent)
    : QWidget(parent)
{
	ui.setupUi(this);
}

/**
 * Set the list the Recordset will use.
 */
void Recordset::setList(QList<QMap<QString, QString>*> list)
{
	m_List = list;
	m_Iterator = m_List.begin();
}

/**
 * Returns the size of the recordset.
 */
int Recordset::size()
{
	return m_List.size();
}

/**
 * Move to the first position.
 */
void Recordset::moveFirst()
{
	m_Iterator = m_List.begin();

	emit recordChanged((*m_Iterator)->value("id"));

	updateLabel();
	m_Index = 0;
}

/**
 * Move to the previous position.
 */
void Recordset::movePrevious()
{
	--m_Iterator;

	emit recordChanged((*m_Iterator)->value("id"));

	updateLabel();
	m_Index = m_Index - 1;
}

/**
 * Move to the next position.
 */
void Recordset::moveNext()
{
	++m_Iterator;

	emit recordChanged((*m_Iterator)->value("id"));

	updateLabel();
	m_Index = m_Index + 1;
}

/**
 * Move to the last position.
 */
void Recordset::moveLast()
{
	m_Iterator = m_List.end();
	--m_Iterator;

	emit recordChanged((*m_Iterator)->value("id"));

	updateLabel();
	m_Index = m_List.size() - 1;
}

/**
 * Returns true if it is at the first position.
 */
bool Recordset::isFirst()
{
	return (m_Index == 0);
}

/**
 * Returns true if it is at the last position.
 */
bool Recordset::isLast()
{
	return (m_Index == m_List.size() - 1);
}

/**
 * Emits the recordChanged signal with the actual index position.
 */
void Recordset::refresh()
{
	emit recordChanged((m_List.at(m_Index))->value("id"));
}

/**
 * Updates the label with the actual index position.
 */
void Recordset::updateLabel()
{
	ui.label->setText(QString("%1 de %2").arg(m_Index).arg(m_List.size()));
}
